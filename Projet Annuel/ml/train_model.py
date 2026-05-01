import json
import pandas as pd
from pathlib import Path
import joblib
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
from sklearn.model_selection import train_test_split
from datetime import datetime
from model_config import FEATURES_BASE, NUMERIC_COLUMNS

BASE_DIR = Path("/app/php")
DATASET_PATH = BASE_DIR / "dataset.csv"
OUTPUT_DIR = BASE_DIR / "ml"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

MODEL_PATH = OUTPUT_DIR / "modele_silver_happy.pkl"
FEATURES_PATH = OUTPUT_DIR / "colonnes_features.pkl"
REPORT_JSON_PATH = OUTPUT_DIR / "training_report.json"
REPORT_TEXT_PATH = OUTPUT_DIR / "training_report.txt"

def main():
    df = pd.read_csv(DATASET_PATH)
    
    df_clean = df.drop(columns=["id_utilisateur"])

    X_brut = df_clean.drop(columns=["target_service"])
    y = df_clean["target_service"]

    X_brut = X_brut.filter(items=FEATURES_BASE).copy()

    colonnes_num = X_brut.columns.intersection(["age", "anciennete_mois", "score_satisfaction", "taux_annulation", "nb_interventions_totales", "depense_totale_estimee", "est_abonne"])
    X_brut[colonnes_num] = X_brut[colonnes_num].apply(pd.to_numeric, errors="coerce").fillna(0)
    
    X = pd.get_dummies(X_brut, drop_first=True)

    colonnes_entrainement = X.columns.tolist()
    joblib.dump(colonnes_entrainement, FEATURES_PATH)

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.20, random_state=42, stratify=y)

    modele = RandomForestClassifier(
        n_estimators=100, 
        max_depth=8, 
        class_weight="balanced", 
        random_state=42, 
        n_jobs=-1
    )
    modele.fit(X_train, y_train)
    predictions = modele.predict(X_test)
    accuracy = accuracy_score(y_test, predictions)
    report_text = classification_report(y_test, predictions, zero_division=0)

    joblib.dump(modele, MODEL_PATH)
    REPORT_TEXT_PATH.write_text(report_text, encoding="utf-8")

    REPORT_JSON_PATH.write_text(
        json.dumps({
            "status": "done",
            "dataset_file": DATASET_PATH.name,
            "rows": len(df),
            "train_rows": len(X_train),
            "features_count": len(colonnes_entrainement),
            "accuracy_percent": round(accuracy * 100, 2),
            "classes": sorted([str(c) for c in y.unique()]),
            "trained_at": datetime.now().strftime("%d/%m/%Y %H:%M")
        }, ensure_ascii=False, indent=2),
        encoding="utf-8"
    )

if __name__ == "__main__":
    main()