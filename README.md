# API-Norsys-Presences
api norsys presences portée en php 8

## CONF
```json
{
	"#ENABLE_DEBUG_CONFIG": "",
	"DEBUG": false,

	"#VIEW_ENGINE_CONFIGS": "",
	"VIEW_ENGINE": "\\DI\\enums\\ViewEngines::BLADE|\\DI\\enums\\ViewEngines::SMART",
	"VIEW_DIR": "__ROOT__ . /app/views",
	"VIEW_CACHE_DIR": "__ROOT__ . /app/views/cache",

	"#DATABASE_CONFIGS": "? if you doesn't use database",
	"DB_ENGINE": "Mysql",
	"DB_HOST": "127.0.0.1",
	"DB_PORT": 3306,
	"DB_LOGIN": "<db login>",
	"DB_PASSWORD": "<db password>",
	"DB_NAME": "<db name>",

	"#EMAILS_CONFIGS": "? if you doesn't use mailer",
	"EMAIL_ENCRIPTION": "?tls|ssl",
	"EMAIL_HOST": "<email host>",
	"EMAIL_PORT": "<email port>",
	"EMAIL": "<your email>",
	"EMAIL_PASSWORD": "<your email password>",
	"EMAIL_NAME": "<name displayed when you send email>"
}
```

## DEV
```bash
cd [workspace]
git clone https://github.com/nicolachoquet06250/API-Norsys-Presences.git ?[project-name]
cd [project-name]
composer install
```
