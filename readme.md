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
        'periods' => 'plans_periods',
        'subscriptions' => 'plans_subscriptions',
        'features' => 'plans_features',
        'usages' => 'plans_subscription_usages'
    ],
    
    //O tus propios modelos que extiendan a los originales
    'models' => [
        'subscription' => \Abrahamf24\PlansSubscriptions\Models\PlanSubscription::class,
        'plan' => \Abrahamf24\PlansSubscriptions\Models\Plan::class,
        'usage' => \Abrahamf24\PlansSubscriptions\Models\PlanSubscriptionUsage::class,
    ]
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
    'type'=>'main' //El tipo de plan, por default "main"
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

# Gestionando suscripciones
Un usuario u otro tipo de modelo puede suscribirse a distintos periodos de planes, es importante tener en cuenta esto ya que se liga al modelo directamente con un periodo de plan y no con un plan.

## Estatus de una suscripción, métodos y scopes
Una suscripción puede tener varios estados dependiendo sus valores, sus estatus son los siguientes:

* **Suscripción cancelada**: Suscripción que ha sido cancelada
* **Suscripción expirada**: Suscripción que ha sido llegado al fin de su ciclo de periodos
* **Suscripción activa**: Suscripción que ya ha iniciado y no ha expirado ni ha sido cancelada
* **Suscripción válida**: Suscripción que ya ha iniciado y no ha sido cancelada, si puede estar expirada
* **Suscripción indefinida**: Suscripción que nunca caduca, en otras palabras no tiene fecha de expiración
* **Suscripción cancelada con periodo válido**: Suscripción cancelada que tiene un periodo que aun no llega a su fecha de expiración

Los métodos disponibles para una suscripción son los siguientes:

* `isIndefinied()`: (`boolean`) Indica si la suscripción es indefinida
* `hasStarted()`: (`boolean`) Indica si la suscripción ha iniciado ya
* `hasExpired()`: (`boolean`) Indica si la suscripción ha expirado
* `expiredDays()`: (`int`) Devuelve la cantidad de días que lleva expirada la suscripción
* `isActive()`: (`boolean`) Indica si la suscripción está activa
* `isValid()`: (`boolean`) Indica si la suscripción es válida
* `remainingDays()`: (`int`) Devuleve la cantidad de días restantes de la suscripción
* `isCancelled()`: (`boolean`) Indica si la suscripción está cancelada
* `isCancellationWithValidPeriod()`: (`boolean`) Indica si la suscripción está cancelada pero tiene un periodo válido

Los [scopes](https://laravel.com/docs/5.7/eloquent#query-scopes) definidos para el modelo son los siguientes:

* `paid()`: Suscripciones pagadas
* `unpaid()`: Suscripciones no pagadas
* `expired()`: Suscripciones expiradas
* `cancelled()`: Suscripciones canceladas
* `notCancelled()`: Suscripciones no canceladas
* `paymentMethod($method)`: Suscripciones por método de pago
* `name($name)`: Suscripciones por nombre
* `active()`: Suscripciones activas

## Acciones principales
Las acciones que se documentan a continuación están disponibles para los modelos que utilizan el trait `HasSubscriptions`

### Suscripción a un periodo de plan
Para suscribirse a un periodo de plan hacer lo siguiente:

```php
$plan_period = $plan->periods()->first(); //Obtener el modelo del periodo de plan
$subscription = $user->subscribeTo($plan_period, 1, 'main');
```

Los parámetros que acepta `subscribeTo` son:
```php
/**
 * @param PlanPeriod $plan_period El periodo de plan a suscribirse
 * @param int $periods Número de periodos a suscribirse
 * @param string $name Nombre de la suscripción, default es "main"
 * @param string $payment_method Método de pago
 * @param boolean $is_paid Indica si la suscripción está pagada, para periodos con precio igual a 0 se define true
 * @return PlanSubscription The PlanSubscription model instance.
 */
public function subscribeTo($plan_period, int $periods = null, $name = 'main', $payment_method=null, $is_paid=false)
```

### Actualizar periodo de plan
Una actualización de plan se conforma de dos acciones, la primera es cancelar la suscripción actual y después suscribir a un periodo de plan nuevo

```php
$new_plan_period = $planBasico->periods()->name('mensual')->first(); //Obtener el nuevo periodo de plan
$subscription = $user->upgradePlanTo($new_plan_period, 1, 'main', 'paypal', true);
```

Los parámetros que acepta `upgradePlanTo` son:
```php
/**
 * @param PlanModel $new_plan_period El nuevo periodo de plan
 * @param int $periods El número de periodos
 * @param string $name El nombre de la suscripción a actualizar
 * @param string $payment_method El método de pago
 * @param bool $is_paid Indica si la suscripción está pagada
 * @return PlanSubscription La nueva suscripción
 */
public function upgradePlanTo($new_plan_period, int $periods = null, $name = 'main', $payment_method=null, $is_paid=false)
```

### Extender suscripción
Una suscripción puede ser extendida por una cantidad de periodos incluso si ya expiró, en el caso de que no exista la suscripción del tipo indicado se crea una nueva, de igual manera si la suscripción del tipo indicado ha sido cancelada

```php
$user->extendSubscription('main', 1, false, true);
```

Los parámetros que acepta `extendSubscription` son:
```php
/**
 * @param string $name          Nombre de la suscripción
 * @param int    $periods       Cantidad de periodos a extender
 * @param bool   $startFromNow  Indica si se extenderá a partir del día actual
 * @return PlanSubscription     El modelo de la suscripción extendida
 */
public function extendSubscription($name='main', $periods, bool $startFromNow = true, $is_paid=false)
```

### Cancelar suscripción
Una suscripción puede ser cancelada en cualquier momento

```php
$user->cancelSubscription('main');
```

Los parámetros que acepta `cancelSubscription` son:
```php
/**
 * @param  string $name Nombre de la suscripción
 * @return boolean
 */
public function cancelSubscription($name='main')
```