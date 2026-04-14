FROM python:3.11-slim AS base

WORKDIR /app/ml

RUN pip install --no-cache-dir pandas scikit-learn joblib pingouin

FROM base AS development

COPY train_model.py /app/ml/train_model.py
CMD ["python", "/app/ml/train_model.py"]

FROM base AS production

COPY train_model.py /app/ml/train_model.py
CMD ["python", "/app/ml/train_model.py"]
