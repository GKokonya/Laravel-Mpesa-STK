@extends('layouts.default')
    @section('content')
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <div class="flex justify-center my-2">
                <h1 class="text-teal-600 text-2xl">Payments</h1>
            </div>
            <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                <thead class="text-left">
                <tr>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> tracking ID</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> Amount</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> Type</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> Status</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Created</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Updated</th>
                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Actions</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse ($payments as $payment)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900"> {{ $payment->tracking_id }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $payment->amount  }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $payment->type }} </td>
                        <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">                
                            @if($payment->status=='pending')
                                <span class="bg-yellow-100 px-2 py-1 rounded">{{ $payment->status }} </span>
                            @endif
                            @if($payment->status=='completed')
                                <span class="bg-teal-100 px-2 py-1 rounded">{{ $payment->status }} </span>
                            @endif
                            @if($payment->status=='failed')
                                <span class="bg-red-100 px-2 py-1 rounded">{{ $payment->status }} </span>
                            @endif
                                
                        </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $payment->created_at }} </td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> {{ $payment->created_at }}</td>
                        <td class="whitespace-nowrap px-4 py-2 text-gray-700"> 
                            @if($payment->status=='pending')
                                <a class="bg-teal-600 px-2  py-1 text-white rounded" href="{{ route('stk-requests.edit', $payment->tracking_id) }}">Activate</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="whitespace-nowrap px-4 py-2 text-gray-700">Loading ...</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endsection
