# Environment Configuration for API

# بعد ترقية PHP وتثبيت L5-Swagger، أضف هذه الإعدادات إلى .env

# L5-Swagger Settings
L5_SWAGGER_GENERATE_ALWAYS=true  # false in production
L5_SWAGGER_CONST_HOST=http://localhost:8000
L5_SWAGGER_USE_ABSOLUTE_PATH=true
L5_FORMAT_TO_USE_FOR_DOCS=json
L5_SWAGGER_GENERATE_YAML_COPY=false

# API Settings
API_URL=http://localhost:8000/api/v1
API_VERSION=v1

# Sanctum Settings (إذا لم تكن موجودة)
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000
SESSION_DRIVER=cookie
SESSION_LIFETIME=120

# CORS Settings (إذا كنت تستخدم Flutter/Mobile)
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8080
CORS_ALLOWED_METHODS=GET,HEAD,POST,PUT,PATCH,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With
