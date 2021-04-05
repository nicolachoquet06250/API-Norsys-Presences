# API-Norsys-Presences
api norsys presences portée en php 8

## CONF
```json
{
	"#ENABLE_DEBUG_CONFIG": "",
	"DEBUG": false,

	"#VIEW_ENGINE_CONFIGS": "",
	"VIEW_ENGINE": "\\DI\\enums\\ViewEngines::BLADE|\\DI\\enums\\ViewEngines::SMARTY",
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

## CREATE NEW CONTROLLER
```php
<?php

namespace DI\[path];

use \DI\decorators\{
	Route, Json
};
use \DI\router\Context;

#[Route('/[path]', methods: ['get', 'post', 'put', 'delete'])]
class MaRoute {
	public function __construct(
		private Context $context
	) {}

	#[Json]
	public function get() {
		return [];
	}

	#[Json]
	public function post() {
		return [];
	}

	#[Json]
	public function put() {
		return [];
	}

	#[Json]
	public function delete() {
		return [];
	}

	// for add new route in the controller
	#[Route('/add_route', method: 'get')]
	#[Json]
	public function additionnal_method() {
		return [];
	}
}
```

## CREATE NEW ENUM
```php
<?php

namespace DI\enums;

abstract class MyEnum {
    const VALUE1 = 'value1';
    const VALUE2 = 'value2';
}

dump(\DI\helpers\ClassAnalyser::inEnum(MyEnum::class, 'value1'));

// output => true

dump(\DI\helpers\ClassAnalyser::inEnum(MyEnum::class, 'value3'));

// output => false
```

## CREATE NEW ATTRIBUTE / DECORATOR
```php
<?php

namespace DI\decorators;

use Attribute;
use DI\bases\AttributeBase;

// for place attribute on classes, methods, properties, parameters, functions, class constantes
#[Attribute(Attribute::TARGET_ALL)]
// for place on classes only
#[Attribute(Attribute::TARGET_CLASS)]
// for place on methods only
#[Attribute(Attribute::TARGET_METHOD)]
// for place on properties only
#[Attribute(Attribute::TARGET_PROPERTY)]
// for place on parameters only
#[Attribute(Attribute::TARGET_PARAMETER)]
// for place on functions only
#[Attribute(Attribute::TARGET_FUNCTION)]
// for place on class constant only
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
// for place on classes and methods only
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
// etc
class MyDecorator extends AttributeBase {
	public function __construct(...$arguments) {
		// management
	}

	public function manage(): void {
		// management
	}
}
```