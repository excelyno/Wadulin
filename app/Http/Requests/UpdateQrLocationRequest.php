<?php

namespace App\Http\Requests;

use App\Models\QrLocation;

class UpdateQrLocationRequest extends StoreQrLocationRequest
{
    public function authorize(): bool
    {
        /** @var QrLocation $qrLocation */
        $qrLocation = $this->route('qrLocation');

        return $this->user()?->can('update', $qrLocation) ?? false;
    }
}
