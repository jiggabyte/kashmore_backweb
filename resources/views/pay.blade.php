@extends('layouts.templater')

@section('homer')
    
  <section class="mbr-section form1 cid-s7VkVNOM2l" id="form1-0" style="display:;">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 class="mbr-section-title align-center pb-3 mbr-fonts-style display-2">
                    Deposit
                </h2>
                <h3 class="mbr-section-subtitle align-center mbr-light pb-3 mbr-fonts-style display-5">
                    How much do you want to Deposit?
                </h3>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="media-container-column col-lg-8" >

            
                    <form class="mbr-form">
                        <input type="hidden" name="uuid" id="uuid" value="" >
                        <div class="row row-sm-offset">
                            <div class="col-md-10 multi-horizontal" data-for="name">
                                <div class="form-group">
                                    <select class="form-control" style="width:70px;" name="name"  required="" id="name">
                                        <option>NGN</option>
                                    </select>
                                    <input type="text" class="form-control" style="width:200px;margin-left:100px;margin-top:-55px;" name="amount"  required="" id="amount">
                                </div>
                            </div>


                        </div>

            
                        <span class="input-group-btn">
                            <button type="submit" id="payBtn" class="btn btn-primary btn-form display-4" style="width:80%;font-size:1.2em!important;">NEXT</button>
                             <p id="notas" style="width:200px;margin:5px auto!important;color:blue!important;"></p>
                        </span>
                       
                    </form>
                    
                    <p style="margin:5px auto!important;color:blue!important;text-align:center">
                    Pay with POS or direct bank deposit<br>
                    2116934185<br>
                    Godwin Chidiebere<br>
                    Zenith bank.<br>       
                    Note: make sure you use your username as sender /depositor's name<br>
                    Click here to Send proof via WhatsApp for confirmation.
                    </p>
                    
                     <form class="mbr-form" style="display:;">
                    <input type="hidden" name="uuidx" id="uuidxi" value="" style="display:;">
                    <div class="row row-sm-offset" style="display:;">
                        <div class="col-md-4 multi-horizontal" data-for="name">
                            <div class="form-group">
                                <hr />
                                
                                <hr />
                                <br />
                                <br />
                                <h4><b><u>Upload payment detail</b></u></h4>
                                <br />
                                <h3 style="text-align:center;">Transfer / Deposit</h3>
                            </div>
                        </div>
                        <div class="col-md-4 multi-horizontal" data-for="bankr">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="bankr-form1-0">Payment Account Name</label>
                                <input type="text" class="form-control" name="bankr" data-form-field="bankr" required="" id="bankr-form1-0">
                            </div>
                        </div>
                        <div class="col-md-4 multi-horizontal" data-for="datr">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="datr-form1-0">Date of Transfer</label>
                                <input type="date" class="form-control" name="datr" data-form-field="datr" id="datr-form1-0">
                            </div>
                        </div>
                        <div class="col-md-4 multi-horizontal" data-for="amtr">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="amtr-form1-0">Amount Paid</label>
                                <input type="text" class="form-control" name="amtr" data-form-field="amtr" required="" id="amtr-form1-0">
                            </div>
                        </div>
                        <div class="col-md-4 multi-horizontal" data-for="loctr">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="loct-form1-0">Location / Branch / Online</label>
                                <input type="text" class="form-control" name="loctr" data-form-field="loctr" id="loctr-form1-0">
                            </div>
                        </div>
                         <div class="col-md-4 multi-horizontal" data-for="refr">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="refr-form1-0">Transaction ID / Teller No. / Ref </label>
                                <input type="text" class="form-control" name="refr" data-form-field="refr" required="" id="refr-form1-0">
                            </div>
                        </div>
                    </div>

                    <span class="input-group-btn">
                            <button type="submit" id="traBnk" class="btn btn-primary btn-form display-4" style="width:80%;font-size:1.2em!important;">SEND</button>
                            <p id="notaxr" style="width:200px;margin:5px auto!important;color:blue!important;"></p>
                        </span>
                        
                        
                </form>
            </div>
        </div>
    </div>
    
    

                <form class="mbr-form">
                    <input type="hidden" name="uuidx" id="uuidx" value="" >
                    <div class="row row-sm-offset" style="width:80vw!important;margin:5px auto!important;">
                        <div class="col-md-4 multi-horizontal" data-for="name">
                            <div class="form-group">
                                <br />
                                <br />
                                <h3 style="text-align:center;">Transfer / Me2U</h3>
                            </div>
                        </div>
                        <div class="col-md-4 multi-horizontal" data-for="user">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="user-form1-0">User ID</label>
                                <input type="text" class="form-control" name="user" data-form-field="user" required="" id="user-form1-0">
                            </div>
                        </div>
                        <div class="col-md-4 multi-horizontal" data-for="amount">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="amount-form1-0">Amount</label>
                                <input type="text" class="form-control" name="amount" data-form-field="Amount" id="amount-form1-0">
                            </div>
                        </div>
                    </div>

                    <span class="input-group-btn">
                            <button type="submit" id="payBnk" class="btn btn-primary btn-form display-4" style="width:60%;font-size:1.2em!important;">SEND</button>
                            <p id="notax" style="width:200px;margin:5px auto!important;color:blue!important;"></p>
                        </span>
                        
                        
                </form>
            </div>
        </div>
    </div>
    
    <br><br>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 class="mbr-section-title align-center pb-3 mbr-fonts-style display-2">
                    Move Funds
                </h2>
                <h3 class="mbr-section-subtitle align-center mbr-light pb-3 mbr-fonts-style display-5">
                    How much do you want to transfer?
                </h3>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="media-container-column col-lg-8" >

            
                    <form class="mbr-form">
                        <input type="hidden" name="uuidx" id="uuidx" value="" >
                        <div class="row row-sm-offset">
                            <div class="col-md-10 multi-horizontal" data-for="namex">
                                <div class="form-group">
                                    <select class="form-control" style="width:70px;" name="namex"  required="" id="namex">
                                        <option>NGN</option>
                                    </select>
                                    <input type="text" class="form-control" style="width:200px;margin-left:100px;margin-top:-55px;" name="amountx"  required="" id="amountx">
                                </div>
                            </div>


                        </div>

            
                        <span class="input-group-btn">
                            <button type="submit" id="payBtnx" class="btn btn-primary btn-form display-4" style="width:80%;font-size:1.2em!important;">Move</button>
                             <p id="notasx" style="width:200px;margin:5px auto!important;color:blue!important;"></p>
                        </span>
                       
                    </form>

               
            </div>
        </div>
    </div>
</section>



<script src="{{ asset('assets/web/assets/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/popper/popper.min.js') }}"></script>
<script src="{{ asset('assets/tether/tether.min.js') }}"></script>
<script src="{{ asset('assets/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/smoothscroll/smooth-scroll.js') }}"></script>
<script src="{{ asset('assets/theme/js/script.js') }}"></script>
<script src="{{ asset('assets/formoid/formoid.min.js') }}"></script>

 <!-- <script src="{{ asset('js/inline.js') }}"></script> -->
 
 <script src="https://js.paystack.co/v1/inline.js"></script>
 
  <script type="text/javascript">

var userLoggedIn = getUserLoggedIn();
var userLoggedInToken = getUserLoggedInToken();
//var userLoggedInJSON = JSON.parse(getUserLoggedDetails());

//alert(userLoggedInJSON.email);

function payWithPaystack(usernamer,amt) {

var handler = PaystackPop.setup({
    key: 'pk_live_c1d73eec4fab088649a7e1ffe15e75abf51197c5', //put your public key here
    email: usernamer, //put your customer's email here
    amount: amt*100, //amount the customer is supposed to pay
    currency: "NGN",
      metadata: {
         custom_fields: [
            {
                display_name: "Mobile Number",
                variable_name: "mobile_number",
                value: "phone_number"
            }
         ]
      },
    callback: function (response) {
        //after the transaction have been completed
        //make post call  to the server with to verify payment
        //using transaction reference as post data
        $.ajaxSetup({
                    headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
        $.post("{{ url('/pay') }}", {reference:response.reference,uuid:userLoggedIn}, function(data){
            
            if(data.success == "success") {
                //successful transaction
               // Do stuff;
               document.querySelector('#amount').value = "";
               showDroidToast(data.success);
          //     document.querySelector('#notax').textContent = data.success;
            } else {
                //transaction failed
                showDroidToast("Error: Please, Retry!");
              //  document.querySelector('#notax').textContent = "Error: Please, Retry!";

            }
        });
    },
    onClose: function () {
        //when the user close the payment modal
        //alert('Transaction Cancelled');
        showDroidToast("Transaction Cancelled");
           //     document.querySelector('#notax').textContent = "Transaction Cancelled";

    }
});
handler.openIframe(); //open the paystack's payment modal
}

document.querySelector('#payBtn').onclick = (e) => {
e.preventDefault();
if(document.querySelector('#amount').value <= 0){
    
    showDroidToast("Enter an Amount!");
    return;
} 
            payWithPaystack(userLoggedIn+"@betgamesng.com",document.querySelector('#amount').value);
}

document.querySelector('#payBnk').onclick = (e) => {
e.preventDefault();
if(document.querySelector('#amount-form1-0').value <= 0){
    
    showDroidToast("Enter an Amount!");
    return;
} 
        $.ajaxSetup({
                    headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
        $.post("{{ url('/transfer') }}", {amount:document.querySelector('#amount-form1-0').value,user:document.querySelector('#user-form1-0').value,uuid:userLoggedIn}, function(data){
            if(data.transfer == "success") {
                //successful transaction
              //  console.log(data);
              //  document.querySelector('#notax').textContent = data.transfer;
              //  alert("Successful!");  
                showDroidToast(data.transfer);
                document.querySelector('#amount-form1-0').value = "";
                document.querySelector('#user-form1-0').value = "";
               // Do stuff;
            } else {
                //transaction failed
               // alert("Failure!");   
               
                showDroidToast(data.transfer);
                
             //   document.querySelector('#notax').textContent = data.transfer;

            }
        }).fail(function (jqXHR, textStatus, error) {
        //console.log("Post error: " + jqXHR.responseText);
        showDroidToast(jqXHR.responseText);
      //  document.querySelector('#notax').textContent = jqXHR.responseText;
    });
}


document.querySelector('#traBnk').onclick = (e) => {
e.preventDefault();

        $.ajaxSetup({
                    headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
        $.post("{{ url('/transfer-manual') }}", {bankr:document.querySelector('#bankr-form1-0').value,datr:document.querySelector('#datr-form1-0').value,amtr:document.querySelector('#amtr-form1-0').value,loctr:document.querySelector('#loctr-form1-0').value,refr:document.querySelector('#refr-form1-0').value,uuid:userLoggedIn}, function(data){
            if(data.transfer == "success") {
                //successful transaction
              //  console.log(data);
              //  document.querySelector('#notaxr').textContent = data.transfer;
              //  alert("Successful!");  
                showDroidToast(data.transfer);
                
               // Do stuff;
            } else {
                //transaction failed
               // alert("Failure!");   
               
                showDroidToast(data.transfer);
                
              //  document.querySelector('#notaxr').textContent = data.transfer;

            }
        }).fail(function (jqXHR, textStatus, error) {
        //console.log("Post error: " + jqXHR.responseText);
        showDroidToast(jqXHR.responseText);
       // document.querySelector('#notaxr').textContent = jqXHR.responseText;
    });
}


var clickerNota = false;



    
document.querySelector('#payBtnx').onclick = (e) => {
e.preventDefault();
if(document.querySelector('#amountx').value <= 0){
    
    showDroidToast("Enter an Amount!");
    return;
} 
if(clickerNota == true){
    
    showDroidToast("Please Wait !");
   // document.querySelector('#notax').textContent = "Please Wait !";
    
} else {
showDroidToast("Processing ...");
clickerNota = true;
setTimeout(function(e){
    

        $.ajaxSetup({
                    headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
        $.post("{{ url('/move') }}", {amountx:document.querySelector('#amountx').value,uuidx:userLoggedIn}, function(data){
            if(data.transfer == "success") {
                //successful transaction
              //  console.log(data);
              //  document.querySelector('#notasx').textContent = data.transfer;
              //  alert("Successful!");  
                showDroidToast(data.transfer);
                document.querySelector('#amountx').value = "";
               
               // Do stuff;
               clickerNota = false;
            } else {
                //transaction failed
               // alert("Failure!");   
               
                showDroidToast(data.transfer);
                
              //  document.querySelector('#notasx').textContent = data.transfer;
                clickerNota = false;

            }
        }).fail(function (jqXHR, textStatus, error) {
        //console.log("Post error: " + jqXHR.responseText);
        showDroidToast(jqXHR.responseText);
       // document.querySelector('#notasx').textContent = jqXHR.responseText;
        clickerNota = false;
    });

},3000)
}

}


    function showDroidToast(toast) {
        Android.showToast(toast);
    }

    function getUserLoggedIn() {
        return Android.getUserID();
    }

    function getUserLoggedInToken() {
        return Android.getUserLoggedInToken();
    }
    
    function getUserLoggedDetails() {
       // return Android.getUserJSON();
    }
  </script>
  
  
 
@endsection