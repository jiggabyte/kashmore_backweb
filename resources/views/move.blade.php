@extends('layouts.templater')

@section('homer')
    
  <section class="mbr-section form1 cid-s7VkVNOM2l" id="form1-0" style="display:;">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 class="mbr-section-title align-center pb-3 mbr-fonts-style display-2">
                    Move Funds
                </h2>
                <h3 class="mbr-section-subtitle align-center mbr-light pb-3 mbr-fonts-style display-5">
                    How much do you want to transfer?
                </h3>
                <p style="">Moved Funds must be lest than Withdrawable Balance</p>
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
                            <button type="submit" id="payBtn" class="btn btn-primary btn-form display-4" style="width:80%;font-size:1.2em!important;">Move</button>
                             <p id="notas" style="width:200px;margin:5px auto!important;color:blue!important;"></p>
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

var clickerNota = false;


if(clickerNota){
    
    showDroidToast("Please Wait !");
    document.querySelector('#notax').textContent = "Please Wait !";
    
} else {
    
document.querySelector('#payBtn').onclick = (e) => {
e.preventDefault();
clickerNota = true;
        $.ajaxSetup({
                    headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
        $.post("{{ url('/move') }}", {amount:document.querySelector('#amount-form1-0').value,uuid:userLoggedIn}, function(data){
            if(data.transfer == "success") {
                //successful transaction
              //  console.log(data);
                document.querySelector('#notax').textContent = data.transfer;
              //  alert("Successful!");  
                showDroidToast(data.transfer);
                document.querySelector('#amount-form1-0').value = "";
                
                clickerNota = false;
               
               // Do stuff;
            } else {
                //transaction failed
               // alert("Failure!");   
               
                showDroidToast(data.transfer);
                
                document.querySelector('#notax').textContent = data.transfer;
                
                clickerNota = false;

            }
        }).fail(function (jqXHR, textStatus, error) {
        //console.log("Post error: " + jqXHR.responseText);
        showDroidToast(jqXHR.responseText);
        document.querySelector('#notax').textContent = jqXHR.responseText;
        clickerNota = false;
    });
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
  </script>
  
  
 
@endsection