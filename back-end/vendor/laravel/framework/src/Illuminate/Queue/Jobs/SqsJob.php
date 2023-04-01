<?php
namespace Illuminate\Queue\Jobs;
use Aws\Sqs\SqsClient;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
class SqsJob extends Job implements JobContract
{
    protected $sqs;
    protected $job;
    public function __construct(Container $container, SqsClient $sqs, array $job, $connectionName, $queue)
    {
        $this->sqs = $sqs;
        $this->job = $job;
        $this->queue = $queue;
        $this->container = $container;
        $this->connectionName = $connectionName;
    }
    public function release($delay = 0)
    {
        parent::release($delay);
        $this->sqs->changeMessageVisibility([
            'QueueUrl' => $this->queue,
            'ReceiptHandle' => $this->job['ReceiptHandle'],
            'VisibilityTimeout' => $delay,
        ]);
    }
    public function delete()
    {
        parent::delete();
        $this->sqs->deleteMessage([
            'QueueUrl' => $this->queue, 'ReceiptHandle' => $this->job['ReceiptHandle'],
        ]);
    }
    public function attempts()
    {
        return (int) $this->job['Attributes']['ApproximateReceiveCount'];
    }
    public function getJobId()
    {
        return $this->job['MessageId'];
    }
    public function getRawBody()
    {
        return $this->job['Body'];
    }
    public function getSqs()
    {
        return $this->sqs;
    }
    public function getSqsJob()
    {
        return $this->job;
    }
}
