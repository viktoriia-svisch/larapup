<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
class MongoDbSessionHandler extends AbstractSessionHandler
{
    private $mongo;
    private $collection;
    private $options;
    public function __construct(\MongoDB\Client $mongo, array $options)
    {
        if (!isset($options['database']) || !isset($options['collection'])) {
            throw new \InvalidArgumentException('You must provide the "database" and "collection" option for MongoDBSessionHandler');
        }
        $this->mongo = $mongo;
        $this->options = array_merge([
            'id_field' => '_id',
            'data_field' => 'data',
            'time_field' => 'time',
            'expiry_field' => 'expires_at',
        ], $options);
    }
    public function close()
    {
        return true;
    }
    protected function doDestroy($sessionId)
    {
        $this->getCollection()->deleteOne([
            $this->options['id_field'] => $sessionId,
        ]);
        return true;
    }
    public function gc($maxlifetime)
    {
        $this->getCollection()->deleteMany([
            $this->options['expiry_field'] => ['$lt' => new \MongoDB\BSON\UTCDateTime()],
        ]);
        return true;
    }
    protected function doWrite($sessionId, $data)
    {
        $expiry = new \MongoDB\BSON\UTCDateTime((time() + (int) ini_get('session.gc_maxlifetime')) * 1000);
        $fields = [
            $this->options['time_field'] => new \MongoDB\BSON\UTCDateTime(),
            $this->options['expiry_field'] => $expiry,
            $this->options['data_field'] => new \MongoDB\BSON\Binary($data, \MongoDB\BSON\Binary::TYPE_OLD_BINARY),
        ];
        $this->getCollection()->updateOne(
            [$this->options['id_field'] => $sessionId],
            ['$set' => $fields],
            ['upsert' => true]
        );
        return true;
    }
    public function updateTimestamp($sessionId, $data)
    {
        $expiry = new \MongoDB\BSON\UTCDateTime((time() + (int) ini_get('session.gc_maxlifetime')) * 1000);
        $this->getCollection()->updateOne(
            [$this->options['id_field'] => $sessionId],
            ['$set' => [
                $this->options['time_field'] => new \MongoDB\BSON\UTCDateTime(),
                $this->options['expiry_field'] => $expiry,
            ]]
        );
        return true;
    }
    protected function doRead($sessionId)
    {
        $dbData = $this->getCollection()->findOne([
            $this->options['id_field'] => $sessionId,
            $this->options['expiry_field'] => ['$gte' => new \MongoDB\BSON\UTCDateTime()],
        ]);
        if (null === $dbData) {
            return '';
        }
        return $dbData[$this->options['data_field']]->getData();
    }
    private function getCollection()
    {
        if (null === $this->collection) {
            $this->collection = $this->mongo->selectCollection($this->options['database'], $this->options['collection']);
        }
        return $this->collection;
    }
    protected function getMongo()
    {
        return $this->mongo;
    }
}
