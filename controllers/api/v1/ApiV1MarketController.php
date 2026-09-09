<?php

class ApiV1MarketController extends ApiV1BaseController
{
    public function pulse()
    {
        $force = isset($_GET['refresh']) && $_GET['refresh'] === '1';
        $data = MarketPulseService::getPulseData($force);

        return $this->json([
            'ok'         => true,
            'updated_at' => date('Y-m-d H:i:s'),
            'data'       => $data
        ]);
    }
}
