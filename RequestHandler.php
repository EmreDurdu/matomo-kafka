<?php

namespace Piwik\Plugins\MatomoKafka;

use Exception;
use JsonException;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\BulkTracking\Tracker\Response;
use Piwik\Tracker;
use Piwik\Tracker\RequestSet;
use Piwik\Tracker\Handler;
use Piwik\Tracker\ScheduledTasksRunner;
use Psr\Log\LoggerInterface;
use RdKafka\Conf;
use RdKafka\Producer;

class RequestHandler extends Handler
{

    private $topic;
    private Producer $rk;
    protected $logger;

    public function __construct()
    {
        parent::__construct();
        $this->logger = StaticContainer::get(LoggerInterface::class);
        $conf = new Conf();
        $conf->set('log_level', (string)LOG_DEBUG);
        $conf->set('debug', 'all');
        $this->rk = new Producer($conf);
        $this->rk->addBrokers("10.5.146.196:9092,10.5.146.197:9092");
        $this->topic = $this->rk->newTopic("test");
    }

    public function process(Tracker $tracker, RequestSet $requestSet)
    {
        parent::process($tracker, $requestSet); // TODO: Change the autogenerated stub
    }


    public function onStartTrackRequests(Tracker $tracker, RequestSet $requestSet)
    {
        foreach ($requestSet->getRequests() as $request) {
            try {
                $json = json_encode($request->getParams(), JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                return;
            }
            $this->topic->produce(RD_KAFKA_PARTITION_UA, 0, $json);
        }
        parent::onStartTrackRequests($tracker, $requestSet); // TODO: Change the autogenerated stub
    }

    public function finish(Tracker $tracker, RequestSet $requestSet)
    {
        $this->rk->flush(1000);
        $res = new Tracker\Response();

        return $res->getOutput(); // TODO: Change the autogenerated stub
    }

    public function getResponse(): Tracker\Response
    {
        return parent::getResponse(); // TODO: Change the autogenerated stub
    }

    public function init(Tracker $tracker, RequestSet $requestSet)
    {
        parent::init($tracker, $requestSet); // TODO: Change the autogenerated stub
    }

    public function onAllRequestsTracked(Tracker $tracker, RequestSet $requestSet)
    {
        parent::onAllRequestsTracked($tracker, $requestSet); // TODO: Change the autogenerated stub
    }

    public function onException(Tracker $tracker, RequestSet $requestSet, Exception $e)
    {
        parent::onException($tracker, $requestSet, $e); // TODO: Change the autogenerated stub
    }

    public function setResponse($response)
    {
        parent::setResponse($response); // TODO: Change the autogenerated stub
    }

    public function setScheduledTasksRunner(ScheduledTasksRunner $runner)
    {
        parent::setScheduledTasksRunner($runner); // TODO: Change the autogenerated stub
    }

    /**
     * @return mixed
     */
    public function getRk()
    {
        return $this->rk;
    }

    /**
     * @param mixed $rk
     */
    public function setRk($rk): void
    {
        $this->rk = $rk;
    }


}

