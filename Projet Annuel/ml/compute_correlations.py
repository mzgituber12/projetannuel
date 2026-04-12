import json
from datetime import datetime
from pathlib import Path

import numpy as np
import pandas as pd

BASE_DIR = Path("/app/php")
DATASET_PATH = BASE_DIR / "dataset.csv"
OUTPUT_DIR = BASE_DIR / "ml"
CORRELATION_REPORT_PATH = OUTPUT_DIR / "correlation_report.json"

COLONNE_NUMERIC = [
    "age",
    "anciennete_mois",
    "nb_interventions_totales",
    "taux_annulation",
    "depense_totale_estimee",
    "score_satisfaction",
    "est_abonne",
]

COLONNE_CATEGORIE = [
    "sexe",
    "langue",
    "type_abonnement",
]


def eta_squared_with_target(num_series, target_series):
    data = pd.DataFrame({"x": num_series, "g": target_series}).dropna()
    data = data[data["g"].astype(str).str.strip() != ""]
    if len(data) < 2:
        return 0.0

    overall_mean = data["x"].mean()
    ss_total = ((data["x"] - overall_mean) ** 2).sum()
    if ss_total <= 0:
        return 0.0

    grouped = data.groupby("g")["x"]
    means = grouped.mean()
    counts = grouped.size()
    ss_between = (((means - overall_mean) ** 2) * counts).sum()
    return float(ss_between / ss_total)


def cramers_v(cat_a_series, cat_b_series):
    data = pd.DataFrame({"a": cat_a_series, "b": cat_b_series}).dropna()
    data["a"] = data["a"].astype(str).str.strip()
    data["b"] = data["b"].astype(str).str.strip()
    data = data[(data["a"] != "") & (data["b"] != "")]
    if data.empty:
        return 0.0

    contingency = pd.crosstab(data["a"], data["b"])
    rows, cols = contingency.shape
    if rows < 2 or cols < 2:
        return 0.0

    observed = contingency.to_numpy(dtype=float)
    total = observed.sum()
    row_totals = observed.sum(axis=1, keepdims=True)
    col_totals = observed.sum(axis=0, keepdims=True)
    expected = (row_totals @ col_totals) / total

    with np.errstate(divide="ignore", invalid="ignore"):
        chi2_matrix = np.where(expected > 0, ((observed - expected) ** 2) / expected, 0.0)
    chi2 = float(chi2_matrix.sum())

    k = min(rows - 1, cols - 1)
    if k <= 0 or total <= 0:
        return 0.0

    value = (chi2 / total / k) ** 0.5
    return float(max(0.0, min(1.0, value)))


def _filter_available_columns(columns_list, dataframe):
    return [col for col in columns_list if col in dataframe.columns]


def main():
    if not DATASET_PATH.exists():
        raise FileNotFoundError("Le fichier dataset.csv est introuvable")

    df = pd.read_csv(DATASET_PATH)
    if "target_service" not in df.columns:
        raise ValueError("La colonne target_service est obligatoire dans le dataset")

    numeric_cols = _filter_available_columns(COLONNE_NUMERIC, df)
    categorical_cols = _filter_available_columns(COLONNE_CATEGORIE, df)

    numeric_df = df[numeric_cols].apply(pd.to_numeric, errors="coerce")

    target_encoded, _ = pd.factorize(df["target_service"].astype(str).str.strip())
    numeric_df_with_target = numeric_df.copy()
    numeric_df_with_target["target_service"] = target_encoded
    pearson_columns = numeric_cols + ["target_service"]
    pearson_matrix_df = numeric_df_with_target[pearson_columns].corr(method="pearson").fillna(0.0)

    eta_scores = []
    for col in numeric_cols:
        eta_scores.append(
            {
                "feature": col,
                "score": round(
                    eta_squared_with_target(numeric_df[col], df["target_service"]),
                    4,
                ),
            }
        )
    eta_scores.sort(key=lambda item: item["score"], reverse=True)

    cramers_scores = []
    for col in categorical_cols:
        cramers_scores.append(
            {
                "feature": col,
                "score": round(cramers_v(df[col], df["target_service"]), 4),
            }
        )
    cramers_scores.sort(key=lambda item: item["score"], reverse=True)

    report = {
        "status": "done",
        "dataset_file": DATASET_PATH.name,
        "rows": int(len(df)),
        "numeric_features": pearson_columns,
        "categorical_features": categorical_cols,
        "target_classes_count": int(
            df["target_service"].astype(str).str.strip().replace("", pd.NA).dropna().nunique()
        ),
        "pearson_matrix": [
            [round(float(value), 4) for value in row]
            for row in pearson_matrix_df.reindex(
                index=pearson_columns,
                columns=pearson_columns,
                fill_value=0.0,
            ).to_numpy()
        ],
        "eta_scores": eta_scores,
        "cramers_scores": cramers_scores,
        "generated_at": datetime.now().strftime("%d/%m/%Y %H:%M"),
    }

    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    CORRELATION_REPORT_PATH.write_text(
        json.dumps(report, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )


if __name__ == "__main__":
    main()
