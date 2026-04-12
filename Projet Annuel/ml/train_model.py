import json
from datetime import datetime
from pathlib import Path

import joblib
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report, f1_score, make_scorer
from sklearn.model_selection import RandomizedSearchCV, StratifiedKFold, train_test_split

BASE_DIR = Path("/app/php")
DATASET_PATH = BASE_DIR / "dataset.csv"
OUTPUT_DIR = BASE_DIR / "ml"

MODEL_PATH = OUTPUT_DIR / "modele_silver_happy.pkl"
FEATURES_PATH = OUTPUT_DIR / "colonnes_features.pkl"
REPORT_TEXT_PATH = OUTPUT_DIR / "training_report.txt"
REPORT_JSON_PATH = OUTPUT_DIR / "training_report.json"


def main():
    if not DATASET_PATH.exists():
        raise FileNotFoundError("Le fichier dataset.csv est introuvable")

    df = pd.read_csv(DATASET_PATH)
    rows_count = int(len(df))
    trained_at = datetime.now().strftime("%d/%m/%Y %H:%M")

    REPORT_JSON_PATH.write_text(
        json.dumps(
            {
                "status": "running",
                "dataset_file": DATASET_PATH.name,
                "rows": rows_count,
                "trained_at": trained_at,
            },
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    if "target_service" not in df.columns:
        raise ValueError("La colonne target_service est obligatoire dans le dataset")

    id_columns = []
    if "user_id" in df.columns:
        id_columns.append("user_id")
    if "id_utilisateur" in df.columns:
        id_columns.append("id_utilisateur")

    df_clean = df.drop(columns=id_columns)
    X_brut = df_clean.drop(columns=["target_service"])
    y = df_clean["target_service"]

    # Features retenues pour un meilleur compromis score global / classes faibles
    selected_base_features = [
        "age",
        "sexe",
        "type_abonnement",
        "langue",
        "score_satisfaction",
        "taux_annulation",
        "nb_interventions_totales",
    ]

    available_selected_features = [
        column for column in selected_base_features if column in X_brut.columns
    ]

    if not available_selected_features:
        raise ValueError("Aucune des features sélectionnées n'est présente dans le dataset")

    X_brut = X_brut[available_selected_features]

    categorical_columns = []
    if "sexe" in X_brut.columns:
        categorical_columns.append("sexe")
    if "langue" in X_brut.columns:
        categorical_columns.append("langue")
    if "type_abonnement" in X_brut.columns:
        categorical_columns.append("type_abonnement")

    if categorical_columns:
        X = pd.get_dummies(X_brut, columns=categorical_columns, drop_first=True)
    else:
        X = X_brut.copy()

    colonnes_entrainement = X.columns.tolist()
    joblib.dump(colonnes_entrainement, FEATURES_PATH)

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.20, random_state=42, stratify=y
    )

    cv_splits = 5
    search_iterations = 40

    rf_param_distributions = {
        "n_estimators": [300, 500, 700, 900],
        "max_depth": [10, 14, 18, 24, None],
        "min_samples_split": [2, 4, 6, 8],
        "min_samples_leaf": [1, 2, 3],
        "max_features": ["sqrt", "log2", None],
        "class_weight": [None, "balanced", "balanced_subsample"],
        "bootstrap": [True],
    }

    weak_classes = ["Club Voyage", "Portage repas", "Yoga"]

    def weak_classes_min_recall(y_true, y_pred):
        report = classification_report(y_true, y_pred, output_dict=True, zero_division=0)
        recalls = []
        for class_name in weak_classes:
            if class_name in report and isinstance(report[class_name], dict):
                recalls.append(float(report[class_name].get("recall", 0.0)))
            else:
                recalls.append(0.0)
        return min(recalls) if recalls else 0.0

    def blended_score(y_true, y_pred):
        acc = accuracy_score(y_true, y_pred)
        macro_f1 = f1_score(y_true, y_pred, average="macro", zero_division=0)
        weak_recall = weak_classes_min_recall(y_true, y_pred)
        return (0.75 * acc) + (0.20 * macro_f1) + (0.05 * weak_recall)

    cv = StratifiedKFold(n_splits=cv_splits, shuffle=True, random_state=42)

    search = RandomizedSearchCV(
        estimator=RandomForestClassifier(random_state=42, n_jobs=-1),
        param_distributions=rf_param_distributions,
        n_iter=search_iterations,
        scoring=make_scorer(blended_score, greater_is_better=True),
        cv=cv,
        random_state=42,
        n_jobs=-1,
        verbose=0,
    )

    search.fit(X_train, y_train)

    best_modele = search.best_estimator_
    predictions = best_modele.predict(X_test)

    accuracy = accuracy_score(y_test, predictions)
    macro_f1 = f1_score(y_test, predictions, average="macro", zero_division=0)
    weak_recall = weak_classes_min_recall(y_test, predictions)

    report_text = classification_report(y_test, predictions, zero_division=0)
    report_dict = classification_report(y_test, predictions, output_dict=True, zero_division=0)

    classes = sorted([str(c) for c in y.unique().tolist()])

    joblib.dump(best_modele, MODEL_PATH)
    REPORT_TEXT_PATH.write_text(report_text, encoding="utf-8")

    REPORT_JSON_PATH.write_text(
        json.dumps(
            {
                "dataset_file": DATASET_PATH.name,
                "rows": rows_count,
                "features_count": int(X.shape[1]),
                "selected_base_features": available_selected_features,
                "train_rows": int(len(X_train)),
                "status": "done",
                "cv_splits": cv_splits,
                "search_iterations": search_iterations,
                "accuracy": float(accuracy),
                "accuracy_percent": round(float(accuracy) * 100, 2),
                "f1_macro": float(macro_f1),
                "f1_macro_percent": round(float(macro_f1) * 100, 2),
                "weak_classes_min_recall": float(weak_recall),
                "selected_strategy": "random_forest_single_model_blended_score",
                "best_cv_score": float(search.best_score_),
                "best_params": search.best_params_,
                "classes": classes,
                "report": report_dict,
                "trained_at": trained_at,
            },
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )


if __name__ == "__main__":
    main()
