import json
from datetime import datetime
from pathlib import Path

import numpy as np
import pandas as pd
import pingouin as pg

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

def main():
    if not DATASET_PATH.exists():
        raise FileNotFoundError("Le fichier dataset.csv est introuvable")

    df = pd.read_csv(DATASET_PATH)
    if "target_service" not in df.columns:
        raise ValueError("La colonne target_service est obligatoire dans le dataset")

    numeric_cols = df.columns.intersection(COLONNE_NUMERIC)
    categorical_cols = df.columns.intersection(COLONNE_CATEGORIE)

    numeric_df = df[numeric_cols].apply(pd.to_numeric, errors="coerce")

    target_encoded, _ = pd.factorize(df["target_service"].astype(str).str.strip())
    numeric_df_with_target = numeric_df.copy()
    numeric_df_with_target["target_service"] = target_encoded
    pearson_columns = numeric_cols + ["target_service"]
    pearson_matrix_df = numeric_df_with_target[pearson_columns].corr(method="pearson").fillna(0.0)

    eta_scores = []
    for col in numeric_cols:
        data = df[[col, "target_service"]].dropna()
        non_empty = data[col].astype(str).str.strip() != ""
        data = data[non_empty]
        
        if len(data) >= 2:
            try:
                eta_sq = pg.eta(data["target_service"], data[col])[0]
                eta_sq = float(eta_sq) if not np.isnan(eta_sq) else 0.0
            except Exception:
                eta_sq = 0.0
        else:
            eta_sq = 0.0
            
        eta_scores.append({"feature": col, "score": round(eta_sq, 4)})
    eta_scores.sort(key=lambda item: item["score"], reverse=True)

    cramers_scores = []
    for col in categorical_cols:
        data = df[[col, "target_service"]].dropna()
        non_empty = (data[col].astype(str).str.strip() != "") & (data["target_service"].astype(str).str.strip() != "")
        data = data[non_empty]
        
        if not data.empty and data[col].nunique() >= 2 and data["target_service"].nunique() >= 2:
            try:
                cramers_val = pg.cramers(data[col], data["target_service"])
                cramers_val = float(cramers_val) if not np.isnan(cramers_val) else 0.0
            except Exception:
                cramers_val = 0.0
        else:
            cramers_val = 0.0
            
        cramers_scores.append({"feature": col, "score": round(cramers_val, 4)})
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
