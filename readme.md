# Laravel Planes y Subscripciones
Es un paquete para Laravel que permite la administración de planes, periodos, suscripciones, etc de usuarios u otro tipo de posibles clientes con la posibilidad de conocer los usos que se le ha dado a una suscripción.


# Instalación
Agregar el repositorio en el archivo composer.json
```json
{
    "repositories":[
        {
            "type":"vcs",
            "url":"https://github.com/abrahamf24/plans-subscriptions.git"
        }
    ],
    "require":{
        "abrahamf24/plans-subscriptions": "dev-master"
    }
}
```

Instalar el paquete:
```bash
$ composer update
```

Publicar el archivo de configuración y migraciones:
```bash
$ php artisan vendor:publish --provider="Abrahamf24\PlansSubscriptions\PlansSubscriptionsServiceProvider"
```

Configurar los nombres de las tablas con los que se crearán las migraciones en el archivo de configuración subscriptions.php:
```php
return [
    'tables' => [
        'plans' => 'plans',
        'periods' => 'plan_periods',
        'subscriptions' => 'plan_subscriptions',
        'features' => 'plan_features',
        'usages' => 'plan_subscription_usages'
    ],
];
```

Migrar las tablas:
```bash
$ php artisan migrate
```

Añadir el trait HasSubscriptions a los modelos necesarios:
```php
use Abrahamf24\PlansSubscriptions\Traits\HasSubscriptions;

class User extends Model {
    use HasSubscriptions;
    ...
}
```

# Creando planes
Un plan es la unidad básica pra trabajar con suscripciones. Puedes crear un plan usando el modelo `Abrahamf24\PlansSubscriptions\Models\Plan` o el tuyo propio en caso de heredar.
```php
use Abrahamf24\PlansSubscriptions\Models\Plan;

$plan = Plan::create([
    'name'=>'Gratuito', //El nombre del plan
    'code'=>'gratuito', //El código del plan
    'description'=>'Plan gratuito con características limitadas', // Descripción del plan
    'type'=>'main' //El tipo de plan
]);
```

El código de plan se puede repetir al de otros planes siempre y cuando pertenezcan a distintos tipos, en este caso por default se utiliza `main` 

# Periodos de plan
Un plan puede tener distintos periodos de pago y pueden variar sus precios entre ellos, el plan más simple debe contar con un periodo para que los usuarios puedan suscribirse a ellos.
La manera de crear periodos es la sigiente:
```php
use Abrahamf24\PlansSubscriptions\Models\PlanPeriod;

$plan->periods()->saveMany([
    new PlanPeriod([
        'name'=>'gratuito',
        'price'=>null,
        'currency'=>'MXN',
        'period_unit'=>null,
        'period_count'=>null,
        'is_recurring'=>false
    ])
]);

$planBasico->periods()->saveMany([
    new PlanPeriod([
        'name'=>'Mensual',
        'price'=>100,
        'currency'=>'MXN',
        'period_unit'=>'month',
        'period_count'=>1,
        'is_recurring'=>true
    ]),
    new PlanPeriod([
        'name'=>'Trimestral',
        'price'=>250,
        'currency'=>'MXN',
        'period_unit'=>'month',
        'period_count'=>3,
        'is_recurring'=>true
    ])
]);
```

Hay varias cosas a tomar en cuenta al crear un periodo de plan:
* **Precio.** Si se desea crear un periodo gratuito el precio se debe definir a cero, cuando es definido como gratuito automáticamente se definirá como no recurrente.
* **Periodo.** Si no se proporciona la unidad de periodo(`month` o `day`) o la cantidad de periodo estos dos serán definidos como nulos
indicando al periodo como indefinido, **no caduca**.
* **Recurrencia.** Cuando es definido a `true` significa que cuando alguien se suscribe deberá renovar la suscripción después del periodo indicado, en otro caso solo se tomará un periodo.


# Features
Las caracetrísticas de plan son importantes para llevar un control de permisos o accesos que tiene un plan, sin importar la cantidad de periodos que tenga un plan las características serán las mismas.
Un plan puede no tener features.

Un feature puede ser de dos tipos:
* `limit` indica que el feature es finito y se podrá llevar un conteo del uso de dicha feature.
* `feature` indica que no es finito, más entendido como un permiso.

La manera de crear features es la siguiente:
```php
use Abrahamf24\PlansSubscriptions\Models\PlanFeature;

$planBasico->features()->saveMany([
    new PlanFeature([
        'name'=>'Imágenes en galería',
        'code'=>'galeria',
        'description'=>'Número de imágenes en galeria',
        'type'=>'limit',
        'limit'=>10,
        'metadata'=>[
            'order'=>1
        ]
    ]),
    new PlanFeature([
        'name'=>'Feed',
        'code'=>'feed',
        'description'=>'Hay Feed',
        'type'=>'feature',
        'metadata'=>[
            'order'=>2
        ]
    ])
]);
```