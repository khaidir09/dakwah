<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OneSignalService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class OneSignalServiceTest extends TestCase
{
    public function test_send_notification_constructs_correct_payload()
    {
        Config::set('services.onesignal.app_id', 'test_app_id');
        Config::set('services.onesignal.rest_api_key', 'test_api_key');

        Http::fake([
            'onesignal.com/*' => Http::response(['id' => 'test_id', 'recipients' => 1], 200),
        ]);

        $service = new OneSignalService();
        $result = $service->sendNotification('Test Title', 'Test Message', ['user_1']);

        $this->assertEquals(['id' => 'test_id', 'recipients' => 1], $result);

        Http::assertSent(function ($request) {
            return $request->url() == 'https://onesignal.com/api/v1/notifications' &&
                   $request->hasHeader('Authorization', 'Basic test_api_key') &&
                   $request->data()['app_id'] == 'test_app_id' &&
                   $request->data()['headings']['en'] == 'Test Title' &&
                   $request->data()['include_aliases']['external_id'][0] == 'user_1';
        });
    }

    public function test_send_to_all_constructs_correct_payload()
    {
        Config::set('services.onesignal.app_id', 'test_app_id');
        Config::set('services.onesignal.rest_api_key', 'test_api_key');

        Http::fake([
            'onesignal.com/*' => Http::response(['id' => 'test_id', 'recipients' => 10], 200),
        ]);

        $service = new OneSignalService();
        $result = $service->sendToAll('Test All', 'Message All');

        $this->assertEquals(['id' => 'test_id', 'recipients' => 10], $result);

        Http::assertSent(function ($request) {
            return $request->data()['included_segments'][0] == 'Total Subscriptions';
        });
    }

    public function test_empty_data_is_omitted_from_payload()
    {
        Config::set('services.onesignal.app_id', 'test_app_id');
        Config::set('services.onesignal.rest_api_key', 'test_api_key');

        Http::fake([
            'onesignal.com/*' => Http::response(['id' => 'test_id'], 200),
        ]);

        $service = new OneSignalService();
        $service->sendNotification('Title', 'Message', ['1']);
        $service->sendToAll('Title', 'Message');

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => !array_key_exists('data', json_decode($request->body(), true)));
        Http::assertNotSent(fn ($request) => str_contains($request->body(), '"data":[]'));
    }

    public function test_non_empty_data_is_sent_as_json_object()
    {
        Config::set('services.onesignal.app_id', 'test_app_id');
        Config::set('services.onesignal.rest_api_key', 'test_api_key');

        Http::fake([
            'onesignal.com/*' => Http::response(['id' => 'test_id'], 200),
        ]);

        (new OneSignalService())->sendToAll('Title', 'Message', null, ['type' => 'haul']);

        Http::assertSent(fn ($request) => str_contains($request->body(), '"data":{"type":"haul"}'));
    }
}
