@extends('layouts.templater')

@section('homer')
    
  <section class="mbr-section form1 cid-s7VkVNOM2l" id="form1-0">




    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 class="mbr-section-title align-center pb-3 mbr-fonts-style display-2">
                    Withdraw
                </h2>
                <h3 class="mbr-section-subtitle align-center mbr-light pb-3 mbr-fonts-style display-5">
                    How much do you want to Withdraw?
                </h3>
                <p>You will be charged ₦50 for any withdrawal</p>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="media-container-column col-lg-8" data-form-type="formoid">
                <div data-form-alert="" hidden="">
                    Thanks for filling out the form!
                </div>
                
                            <!-- The Modal -->
                            <div class="modal" id="nameModal">

                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">

                                  <!-- Modal Header -->
                                  <div class="modal-header">
                                    <h4 class="modal-title">Confirm Account Name.</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                  </div>

                                  <!-- Modal body -->
                                  <div class="modal-body">
                                      <h6 id="namer"></h6>
                                    <p>Click Confirm to continue transaction or Cancel to go back?</p>
                                    
                                  </div>

                                  <!-- Modal footer -->
                                  <div class="modal-footer">
                                  <button id="canceller" type="button" class="btn btn-success" style="position:absolute;left:15px;" data-dismiss="modal">Cancel</button>

                                  <button id="confirmer" type="button" class="btn btn-info" onclick=''; data-dismiss="modal">Confirm</button>
                                  </div>

                                </div>
                              </div>
                            </div>  

                <form class="mbr-form" action="#" method="post" data-form-title="Mobirise Form"><input type="hidden" name="email" data-form-email="true" value="c0JaVP90copFPuOPPfVUHMwWk16uE/cwrz77iJ2M71Mp15jKEl+WlfniLz0itPkTyrA6FV0b0xziNco1gKrg8b/vM3AJwlA+XJcjzf2pcPObIa/TdKT71tSKWDZh1/IY" data-form-field="Email">
                    <div id="namesy" class="row row-sm-offset">
                        <div class="col-md-10 multi-horizontal" data-for="name">
                            <div class="form-group">
                                <select class="form-control" style="width:130px;" name="name" data-form-field="Name" required="" id="name-form1-0">
                                    <option>NGN</option>
                                </select>
                                <input type="text" class="form-control" style="width:200px;margin-left:150px;margin-top:-55px;" name="amount" data-form-field="Amount" required="" id="amount">
                            </div>
                        </div>
                    </div>
                    <div id="accsy" class="row row-sm-offset">
                        <div class="col-md-10 multi-horizontal" data-for="acname">
                            <div class="form-group">
                                <select class="form-control" style="width:130px;" name="acname" data-form-field="AccountName" required="" id="acname-form1-0">
                                    <option>Account No.</option>
                                </select>
                                <input type="text" class="form-control" style="width:200px;margin-left:150px;margin-top:-55px;" name="acdigits" data-form-field="Acdigits" required="" id="acdigits">
                            </div>
                        </div>
                    </div>
                    <div id="banksy" class="row row-sm-offset">
                        <div class="col-md-10 multi-horizontal" data-for="banks">
                            <div class="form-group">
                                <select class="form-control" style="margin:0 auto;width:270px;display:none;" name="banks" data-form-field="Bank" id="bank-form1-0">
                                    <option value="banks" disabled>List of Banks</option>
                                 
                                </select>
                                
                            </div>
                        </div>
                    </div>
                    <span class="input-group-btn">
                            <button type="submit" id="payWth" class="btn btn-primary btn-form display-4" style="width:80%;font-size:1.2em!important;">NEXT</button>
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

  
  <script type="text/javascript">

var userLoggedIn = getUserLoggedIn();
var userLoggedInToken = getUserLoggedInToken();

document.querySelector('#payWth').onclick = (e) => {
e.preventDefault();

           
            
            window.localStorage.setItem('montant',document.querySelector('#amount').value);
            window.localStorage.setItem('numero',document.querySelector('#acdigits').value);
            
            if(Number(window.localStorage.getItem('montant')) < 500){
                showDroidToast('Increase Withdrawal To or Above ₦500.');
                 return;
                
            } 
        
            
             showDroidToast('Checking ...');
    
        $.ajaxSetup({
                    headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
        $.post("{{ url('/getbanks') }}", {uuid:userLoggedIn}, function(data){
            
            if(data.bank.success == "success") {
                //successful transaction
                //console.log(JSON.parse(data.bank.bank).message);
                showDroidToast('Getting list of Banks ...');
                 
                    document.querySelector('#name-form1-0').style.display = 'none';
                    document.querySelector('#amount').style.display = 'none';
                    document.querySelector('#acname-form1-0').style.display = 'none';
                    document.querySelector('#acdigits').style.display = 'none';
                    document.querySelector('#payWth').style.display = 'none';
                    document.querySelector('#bank-form1-0').style.display = 'block';
                    
                    var newSelect = document.querySelector('#bank-form1-0');
                    
                    try {
                            for(item in JSON.parse(data.bank.bank).data)
                       {
                      
                        var opt = document.createElement("option");
                        opt.value = JSON.parse(data.bank.bank).data[item].code;
                        opt.innerHTML = JSON.parse(data.bank.bank).data[item].name; // whatever property it has
                        
                        // then append it to the select element
                        newSelect.appendChild(opt);
                    
                      }
                    }
                    catch(err) {
                        console.log(err.message);
                        window.location = "{{ url('/withdraw') }}";
                    }
                  
                    

                    // then append the select to an element in the dom
                    // Do stuff;
                    
                    document.querySelector('#bank-form1-0').onchange = (e) => {
                        
                        window.localStorage.setItem('banky',document.querySelector('#bank-form1-0').value);
                        
                        $.ajaxSetup({
                            headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.post("{{ url('/resolve') }}", {uuid:userLoggedIn,account:window.localStorage.getItem('numero'),bank_code:window.localStorage.getItem('banky')}).done(function(data){
                            if(data.bank.success == "success") {
                                //successful transaction
                                //console.log(data);
                                showDroidToast('Getting Account Name ...');
                
                                if(JSON.parse(data.bank.resolver).status != true){
                                    showDroidToast('Account Name Failed!');
                                    window.location = "{{ url('/withdraw') }}";
                                } else {
                                    
                                    $('#nameModal').modal('show');
                                    document.querySelector('#namer').textContent = "Account Name: "+JSON.parse(data.bank.resolver).data.account_name;
                                    
                                   // var accounter = confirm("Account Name: "+JSON.parse(data.bank.resolver).data.account_name);
                                    
                                 document.querySelector('#confirmer').onclick = function(){
                                        
                                        
                                        $.ajaxSetup({
                                            headers: {
                                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                            }
                                        });
                                        $.post("{{ url('/create-recipient') }}", {uuid:userLoggedIn,acct_name:JSON.parse(data.bank.resolver).data.account_name,account:JSON.parse(data.bank.resolver).data.account_number,typer:"nuban",bnk_code:window.localStorage.getItem('banky'),curr:"NGN",details:JSON.parse(data.bank.resolver).data.account_name_+" withdrawal"}, function(data){
                                            if(JSON.parse(data.recipient).status) {
                                                //successful transaction
                                                //console.log(data);
                                                showDroidToast('Loading ...');
                
                                               
                                                $.ajaxSetup({
                                                    headers: {
                                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                                    }
                                                });
                                                $.post("{{ url('/withdraw-money') }}", {uuid:userLoggedIn, amt:window.localStorage.getItem('montant'), reci_code:JSON.parse(data.recipient).data.recipient_code, reason:"withdrawal"}, function(data){
                                                    if(JSON.parse(data.withdraw).success == "success") {
                                                        //successful transaction
                                                        //console.log(data);
                                                        showDroidToast('Processing ...');
                
                                                        if(JSON.parse(data.withdraw).status != true){
                                                            //transaction failure
                                                            showDroidToast('Failure: ');
                                                            window.location = "{{ url('/withdraw') }}";
                                                        } else {
                                                    
                                                            //transaction success
                                                            showDroidToast('Withdrawal Successful!');
                                                            window.location = "{{ url('/withdraw') }}";
                                
                                                        }
                
                
                
                                                    } else {
                                                        //transaction failed
                                                        showDroidToast('Failed, please retry!');
                                                        window.location = "{{ url('/withdraw') }}";

                                                    }
                                                }).fail(function (jqXHR, textStatus, error) {
                                                    //console.log("Post error: " + jqXHR.responseText);
                                                     showDroidToast('Error, please retry!'+jqXHR.responseText);
                                                     window.location = "{{ url('/withdraw') }}";
                                                });
                  
                
                
                                            } else {
                                                //transaction failed
                                                showDroidToast('Error, please retry!');
                                                window.location = "{{ url('/withdraw') }}";

                                            }
                                        }).fail(function (jqXHR, textStatus, error) {
                                            //console.log("Post error: " + jqXHR.responseText);
                                             showDroidToast('Error, please retry!'+jqXHR.responseText);
                                             window.location = "{{ url('/withdraw') }}";
                                        });
                                        
                                        
                                        
                                    };
                                    
                                    document.querySelector('#canceller').onclick = function(){
                                         $('#nameModal').modal('hide');
                                         window.location = "{{ url('/withdraw') }}";
                                    };
                  
                                
                                }
                
                
                
                            } else {
                                //transaction failed
                                showDroidToast('Error, please retry!');
                                window.location = "{{ url('/withdraw') }}";

                            }
                        }).fail(function (jqXHR, textStatus, error) {
                            //console.log("Post error: " + jqXHR.responseText);
                             showDroidToast('Error, please retry! '+jqXHR.responseText);
                             window.location = "{{ url('/withdraw') }}";
                        });
                    
                        
                    }
                
                
                
            } else {
                //transaction failed
                showDroidToast('Retrieval Error, please retry!');
                window.location = "{{ url('/withdraw') }}";

            }
        }).fail(function (jqXHR, textStatus, error) {
        //console.log("Post error: " + jqXHR.responseText);
         showDroidToast('Error, please retry!'+jqXHR.responseText);
         window.location = "{{ url('/withdraw') }}";
    });

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