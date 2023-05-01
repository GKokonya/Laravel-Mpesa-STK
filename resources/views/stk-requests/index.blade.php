@extends('layouts.default')
    @section('content')
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <div class="flex justify-center my-2">
                <h1 class="text-teal-600 text-2xl">STK Requests</h1>
            </div>
            <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                <thead class="text-left">
                <tr>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> merchantRequestID</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> checkoutRequestID</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> responseDescription</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> customerMessage</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> status</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> resultCode</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> resultDesc</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> amount</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> mpesaReceiptNumber</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> balance</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> transactionDate</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> phoneNumber</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Created</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Updated</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse ($stkRequests as $stkRequest)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> {{ $stkRequest->merchantRequestID }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->checkoutRequestID  }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->responseDescription }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->customerMessage }} </td>
                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">                
                            @if($stkRequest->status=='requested')
                                <span class="bg-yellow-100 px-2 py-1 rounded">{{ $stkRequest->status }} </span>
                            @endif
                            @if($stkRequest->status=='paid')
                                <span class="bg-teal-100 px-2 py-1 rounded">{{ $stkRequest->status }} </span>
                            @endif
                            @if($stkRequest->status=='failed')
                                <span class="bg-red-100 px-2 py-1 rounded">{{ $stkRequest->status }} </span>
                            @endif
                                
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->resultCode  }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->resultDesc }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->amount }} </td>
                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> {{ $stkRequest->mpesaReceiptNumber }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->balance  }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->transactionDate }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->phoneNumber }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->created_at }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $stkRequest->updated_at }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="" class="whitespace-nowrap px-4 py-2 text-gray-700">Loading ...</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endsection
