FROM python:3.11-slim AS base

WORKDIR /app/ml

RUN pip install --no-cache-dir pandas scikit-learn joblib pingouin

FROM base AS development

COPY train_model.py /app/ml/train_model.py
COPY model_config.py /app/ml/model_config.py
COPY prediction_client.py /app/ml/prediction_client.py
CMD ["python", "/app/ml/prediction_client.py"]

FROM base AS production

COPY train_model.py /app/ml/train_model.py
COPY model_config.py /app/ml/model_config.py
COPY prediction_client.py /app/ml/prediction_client.py
CMD ["python", "/app/ml/prediction_client.py"]
