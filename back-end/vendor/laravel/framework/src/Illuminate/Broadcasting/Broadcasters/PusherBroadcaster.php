<?php
namespace Illuminate\Broadcasting\Broadcasters;
use Pusher\Pusher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Broadcasting\BroadcastException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
class PusherBroadcaster extends Broadcaster
{
    protected $pusher;
    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }
    public function auth($request)
    {
        if (Str::startsWith($request->channel_name, ['private-', 'presence-']) &&
            ! $request->user()) {
            throw new AccessDeniedHttpException;
        }
        $channelName = Str::startsWith($request->channel_name, 'private-')
                            ? Str::replaceFirst('private-', '', $request->channel_name)
                            : Str::replaceFirst('presence-', '', $request->channel_name);
        return parent::verifyUserCanAccessChannel(
            $request, $channelName
        );
    }
    public function validAuthenticationResponse($request, $result)
    {
        if (Str::startsWith($request->channel_name, 'private')) {
            return $this->decodePusherResponse(
                $request, $this->pusher->socket_auth($request->channel_name, $request->socket_id)
            );
        }
        return $this->decodePusherResponse(
            $request,
            $this->pusher->presence_auth(
                $request->channel_name, $request->socket_id,
                $request->user()->getAuthIdentifier(), $result
            )
        );
    }
    protected function decodePusherResponse($request, $response)
    {
        if (! $request->input('callback', false)) {
            return json_decode($response, true);
        }
        return response()->json(json_decode($response, true))
                    ->withCallback($request->callback);
    }
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $socket = Arr::pull($payload, 'socket');
        $response = $this->pusher->trigger(
            $this->formatChannels($channels), $event, $payload, $socket, true
        );
        if ((is_array($response) && $response['status'] >= 200 && $response['status'] <= 299)
            || $response === true) {
            return;
        }
        throw new BroadcastException(
            is_bool($response) ? 'Failed to connect to Pusher.' : $response['body']
        );
    }
    public function getPusher()
    {
        return $this->pusher;
    }
}
