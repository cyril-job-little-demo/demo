<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Fig\Http\Message\StatusCodeInterface;
use App\ErrorHandlerMiddleware;
use App\CorsMiddleware;
use App\RateLimitingMiddleware;
use Symfony\Component\HttpClient\HttpClient;
use Slim\Psr7\Stream;
use Money\Parser\DecimalMoneyParser;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

require __DIR__ . '/../vendor/autoload.php';

$rubyServiceUrl = '%RUBY_SERVICE_URL%';
$goServiceUrl = '%GO_SERVICE_URL%';
$scalaServiceUrl = '%SCALA_SERVICE_URL%';

$app = AppFactory::create();

$app->add(new RateLimitingMiddleware());
$app->addBodyParsingMiddleware();
$app->add(new CorsMiddleware());
$errorMiddleware = $app->addErrorMiddleware(false, false, false);
$errorMiddleware->setDefaultErrorHandler(new ErrorHandlerMiddleware());

$app->get('/stripe/api/balance', function (Request $request, Response $response, $args) use ($scalaServiceUrl) {
    $http = HttpClient::create();
    $cloudResponse = $http->request('GET', $scalaServiceUrl.'/balance');

    return $response
        ->withBody(new Stream($cloudResponse->toStream()))
        ->withStatus($cloudResponse->getStatusCode());
});

$app->get('/stripe/api/balance/transactions', function (Request $request, Response $response, $args) use ($goServiceUrl) {
    $http = HttpClient::create();
    $cloudResponse = $http->request('GET', $goServiceUrl.'/balance/transactions');

    return $response
        ->withBody(new Stream($cloudResponse->toStream()))
        ->withStatus($cloudResponse->getStatusCode());
});

$app->post('/stripe/api/charge/create', function (Request $request, Response $response, $args) use ($rubyServiceUrl) {
    $data = $request->getParsedBody();

    if (isset($data['description']) === false) {
        throw new Exception('Description required', StatusCodeInterface::STATUS_BAD_REQUEST);
    }

    if (isset($data['amount']) === false) {
        throw new Exception('Amount required', StatusCodeInterface::STATUS_BAD_REQUEST);
    }

    if ( ! is_numeric($data['amount'])) {
        throw new Exception('Invalid Amount', StatusCodeInterface::STATUS_BAD_REQUEST);
    }

    if ( ! is_string($data['description']) || strlen($data['description']) > 50) {
        throw new Exception('Invalid Description', StatusCodeInterface::STATUS_BAD_REQUEST);
    }

    $moneyParser = new DecimalMoneyParser(new ISOCurrencies());
    $money = $moneyParser->parse($data['amount'], new Currency('GBP'));

    $http = HttpClient::create();
    $cloudResponse = $http->request('POST', $rubyServiceUrl.'/charge/create', [
        'json' => [
            'description' => $data['description'],
            'amount' => $money->getAmount()
        ]
    ]);

    return $response
        ->withBody(new Stream($cloudResponse->toStream()))
        ->withStatus($cloudResponse->getStatusCode());
});

$app->run();