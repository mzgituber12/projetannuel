import json
import os
import sys
from pathlib import Path

import joblib
import pandas as pd

from model_config import FEATURES_BASE, NUMERIC_COLUMNS

BASE_DIR = Path(os.getenv("ML_BASE_DIR", "/app/php"))
MODEL_PATH = BASE_DIR / "ml" / "modele_silver_happy.pkl"
FEATURES_PATH = BASE_DIR / "ml" / "colonnes_features.pkl"

model = None
training_columns = None


def charger_donnees():
    global model, training_columns
    model = joblib.load(MODEL_PATH)
    training_columns = joblib.load(FEATURES_PATH)


def normaliser_payload(payload):
    normaliser = {}
    for key in FEATURES_BASE:
        normaliser[key] = payload.get(key)

    numeric_defaults = {
        "age": 65,
        "anciennete_mois": 0,
        "score_satisfaction": 4.0,
        "taux_annulation": 0.0,
        "nb_interventions_totales": 0,
        "depense_totale_estimee": 0.0,
        "est_abonne": 0,
    }
    for key in NUMERIC_COLUMNS:
        default_value = numeric_defaults.get(key)
        val_brut = normaliser.get(key)
        try:
            normaliser[key] = float(val_brut)
        except (TypeError, ValueError):
            normaliser[key] = float(default_value)

    normaliser["sexe"] = str(normaliser.get("sexe") or "F")
    normaliser["type_abonnement"] = str(normaliser.get("type_abonnement") or "None")
    normaliser["langue"] = str(normaliser.get("langue") or "fr")
    return normaliser


def prediction_payload(payload):
    normaliser = normaliser_payload(payload)
    df_brut = pd.DataFrame([normaliser]).filter(items=FEATURES_BASE)
    encoded = pd.get_dummies(df_brut, drop_first=True)
    encoded = encoded.reindex(columns=training_columns, fill_value=0)

    probabilities = model.predict_proba(encoded)[0]
    classes = list(model.classes_)
    pairs = list(zip(classes, probabilities))
    pairs.sort(key=lambda x: x[1], reverse=True)

    recommendations = []
    for label, prob in pairs[:5]:
        recommendations.append({
            "service_trouver": str(label),
            "score": round(float(prob), 4),
        })

    if recommendations:
        principal = recommendations[0]
    else:
        principal = {"service_trouver": "", "score": 0.0}
    if len(recommendations) > 1:
        alternatives = recommendations[1:]
    else:
        alternatives = []
    return {
        "principal": principal,
        "alternatives": alternatives,
        "used_features": normaliser,
    }


def main():
    charger_donnees()
    payload_brut = sys.stdin.read()
    payload = json.loads(payload_brut or "{}")
    result = prediction_payload(payload)
    print(json.dumps(result, ensure_ascii=False))


if __name__ == "__main__":
    main()
