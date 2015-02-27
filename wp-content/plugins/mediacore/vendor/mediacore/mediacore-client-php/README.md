# MediaCore Client PHP #

MediaCore PHP LTI, OAuth, and Client libraries

## Dependencies ##

- PHP 5.3.3 or greater.
- [Requests for PHP](https://github.com/rmccue/Requests)
- [Zend\Uri](http://framework.zend.com/manual/2.3/en/modules/zend.uri.html) Zend Framework 2 package


## Installation ##

You can install all dependencies using [composer](https://getcomposer.org/):

```
brew install composer

# install without dev dependencies
composer install --no-dev

# install with dev dependencides
composer install --dev
```

Composer contains an autoloader for the required php libraries that can be included in your project like so:

``` php
require_once('vendor/autoload.php');
```

### Example ###

```php
require_once('vendor/autoload.php');

$key = 'sample-key';
$secret = 'sample-secret';
$auth = new MediaCore\Auth\Lti($key, $secret);

$baseUrl = 'http://localhost:8080';
$client = new MediaCore\Http\Client($baseUrl, $auth);

$params = array(
	'context_id' => 'my_context_id',
	'context_label' => 'test_course_label',
	'context_title' => 'test_course_title',
	'ext_lms' => 'moodle-2',
	'lis_person_name_family' => 'test_user',
	'lis_person_name_full' => 'test_name_full',
	'lis_person_name_given' => 'test_name_given',
	'lis_person_contact_email_primary' => 'test_email',
	'lti_message_type' => 'basic-lti-launch-request',
	'roles' => 'Instructor',
	'tool_consumer_info_product_family_code' => 'moodle',
	'tool_consumer_info_version' => '1.0',
	'user_id' => '101',
);

$endpoint = 'chooser';
$url = $client->getUrl($endpoint) . '?' . $client->getQuery($params);
$response = $client->get($url);

if ($response->success) {
	var_dump($response->body);
} else {
	echo 'Error Status: ' . $response->statusCode;
}
```

## Tests ##

The tests use [PHPUnit 4.1.*](http://phpunit.de/). See installing dev dependencies in the installation section above.

Once installed, you can run all tests with:

```
cd tests
phpunit --debug
```

## Documentation ##

Documentation is created using [ApiGen](http://apigen.org/). See installing dev dependencies in the installation section above.

Once installed, you can build documention with:

```
mkdir -p docs/
./vendor/bin/apigen.php --source /src --destination /docs
```