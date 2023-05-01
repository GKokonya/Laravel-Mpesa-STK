@extends('layouts.default')
  @section('content')
      <!-- EventList Section -->
      <section>
          <div class="rounded shadow-lg bg-gray-300 px-4 py-4 my-4">
              <h1 class="font-bold text-2xl">Your payment is being processed</h1>
              <h1 class="font-medium text-md">If you have made M-PESA payment, please check your phone and complete the transaction</h1>
              <x-loader/>
          </div>
      </section>

      
    <script >


    let checkoutRequestID ="{{ Session::get('checkoutRequestID') }}";

    let myInterval=setInterval( () => { 
        const form =  {checkoutRequestID:checkoutRequestID};
        fetchlnmo(form,myInterval)
    }, 3000);


    let fetchlnmo = (checkoutRequestID,stopInterval) =>{
        axios.post("{{ route('stk-requests.confirm') }}", checkoutRequestID)
        .then((response) => {
            if(response.data.resultCode=='0'){
                clearInterval(stopInterval);
                window.location.href = "{{ route('stk-requests.success') }}";
            }

            if(response.data.resultCode!='0' && response.data.resultCode!=null){
                clearInterval(stopInterval);
                window.location.href = "{{ route('stk-requests.failure') }}";
            }

        })
        .catch((error) => {
        console.log(error);
        });
    }

    </script>
  @endsection