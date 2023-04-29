@extends('layouts.default')
    @section('content')

      <div class="mx-auto max-w-screen-xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-lg">
          <h1 class="text-center text-2xl font-bold text-green-600 sm:text-3xl">MPESA</h1>


          <form action="route('donate')" class="mb-0 mt-6 space-y-4 rounded-lg p-4 shadow-lg sm:p-6 lg:p-8">
            @csrf
            <div>
              <label for="price" class="block text-sm font-medium leading-6 text-gray-900">Phone</label>
                <input type="number" name="phone" class="w-full rounded-lg border-gray-200 p-4 pe-12 text-sm shadow-sm" placeholder="2547xxxxxxxxx"/>
            </div>
            <div>
              <label for="price" class="block text-sm font-medium leading-6 text-gray-900">Email</label>
                <input type="email" name="phone" class="w-full rounded-lg border-gray-200 p-4 pe-12 text-sm shadow-sm" value="test@example.com" placeholder="test@example.com"/>
            </div>
            <div>
              <label for="price" class="block text-sm font-medium leading-6 text-gray-900">Amount</label>
                <input type="number" name="amunt" class="w-full rounded-lg border-gray-200 p-4 pe-12 text-sm shadow-sm" value="1" placeholder="amount" />
            </div>

            <button type="submit" class="block w-full rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white">Donate</button>
          </form>

        </div>
      </div>

    </body>
</html>
@endsection