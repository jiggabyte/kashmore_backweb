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
                <p class="mbr-section-subtitle align-center mbr-light pb-3 mbr-fonts-style display-5">You will be charged ₦50 for any withdrawal</p>
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
                                <select class="form-control" style="width:130px;" name="name" data-form-field="Name" required="required" id="name-form1-0">
                                    <option>NGN</option>
                                </select>
                                <input type="text" class="form-control" style="width:200px;margin-left:150px;margin-top:-55px;" name="amount" data-form-field="Amount" required="required" id="amount">
                            </div>
                        </div>
                    </div>
                    <div id="accsy" class="row row-sm-offset">
                        <div class="col-md-10 multi-horizontal" data-for="acnume">
                            <div class="form-group">
                                <select class="form-control" style="width:130px;" name="acnume" data-form-field="AccountNume" required="required" id="acnume-form1-0">
                                    <option>Account No.</option>
                                </select>
                                <input type="text" class="form-control" style="width:200px;margin-left:150px;margin-top:-55px;" name="acdigits" data-form-field="Acdigits" required="required" id="acdigits">
                            </div>
                        </div>
                    </div>
                    <div id="accsy" class="row row-sm-offset">
                        <div class="col-md-10 multi-horizontal" data-for="acname">
                            <div class="form-group">
                                <select class="form-control" style="width:130px;" name="acname" data-form-field="AccountName" required="required" id="acname-form1-0">
                                    <option>Account Name.</option>
                                </select>
                                <input type="text" class="form-control" style="width:200px;margin-left:150px;margin-top:-55px;" name="acnome" data-form-field="Acnome" required="required" id="acnome">
                            </div>
                        </div>
                    </div>
                    <div id="accsy" class="row row-sm-offset">
                        <div class="col-md-10 multi-horizontal" data-for="bkname">
                            <div class="form-group">
                                <select class="form-control" style="width:130px;" name="bkname" data-form-field="BankName" required="required" id="bkname-form1-0">
                                    <option>Bank</option>
                                </select>
                                <input type="text" class="form-control" style="width:200px;margin-left:150px;margin-top:-55px;" name="bknome" data-form-field="Bknome" required="required" id="bknome">
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
                            <button type="submit" id="payWth" class="btn btn-primary btn-form display-4" style="width:80%;font-size:1.2em!important;">Withdraw</button>
                        </span>
                </form>
            </div>
        </div>
    </div>
    
    <br>
    
    <section>
<table class="table">
  <thead class="thead-dark">
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Amount</th>
      <th scope="col">Status</th>
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
      @if(count($withdraw_data) == 0)
      
      <tr>
      <th scope="row"> - </th>
      <td id="amt"> - </td>
      <td id="nte"> - </td>
      <td> - No Data - </td>
    </tr>
      
      @endif
      
      @foreach($withdraw_data as $withdraw_datum)
      
    <tr>
      <th scope="row">{{ "R".((int)$withdraw_datum->id * 2 - 1500) }}</th>
      <td id="amt{{ $withdraw_datum->id }}">{{ $withdraw_datum->amount }}</td>
      <td id="nte{{ $withdraw_datum->id }}">{{ $withdraw_datum->notes }}</td>
      <td><button id="cancel{{ $withdraw_datum->id }}" type="button" class="btn btn-sm btn-primary"> X </button></td>
    </tr>
    
    <script type="text/javascript">

var userLoggedInX = getUserLoggedInX();
var userLoggedInTokenX = getUserLoggedInTokenX();

    document.querySelector("#cancel{{ $withdraw_datum->id }}").onclick = (e) => {
e.preventDefault();

            
             showDroidToastX('Processing ...');
    
        $.ajaxSetup({
                    headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
        $.post("{{ url('/withdraw-cancel') }}", {uuid:userLoggedInX,amount:document.querySelector("#amt{{ $withdraw_datum->id }}").innerHTML,notes:document.querySelector("#nte{{ $withdraw_datum->id }}").innerHTML}, function(data){
            
            if(data.success == "success") {
                //successful transaction
                //console.log(JSON.parse(data.bank.bank).message);
                showDroidToastX('Successful!');
                window.location = "{{ url('/withdrawal?uuid=') }}"+userLoggedInX;
                 
            } else {
                //transaction failed
                showDroidToastX('Error, please retry!');
                
            }
        }).fail(function (jqXHR, textStatus, error) {
        //console.log("Post error: " + jqXHR.responseText);
         showDroidToastX('Error, please retry!'+jqXHR.responseText);
         
    });

}

 function showDroidToastX(toast) {
       Android.showToast(toast);
    }

    function getUserLoggedInX() {
       return Android.getUserID();
    }

    function getUserLoggedInTokenX() {
        return Android.getUserLoggedInToken();
    }
  </script>
    
    @endforeach
    
  </tbody>
</table>

</section>
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
                
            } else if(document.querySelector('#amount').value != "" && document.querySelector('#acdigits').value != "" && document.querySelector('#acnome').value != "" && document.querySelector('#bknome').value != ""){
                
            } else {
                showDroidToast('All Fields are Required!.');
                return;
            }
            
           
             showDroidToast('Processing ...');
    
        $.ajaxSetup({
                    headers: {
                               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
        $.post("{{ url('/withdraw-request') }}", {uuid:userLoggedIn,amount:document.querySelector('#amount').value,acdigits:document.querySelector('#acdigits').value,acnome:document.querySelector('#acnome').value,bknome:document.querySelector('#bknome').value}, function(data){
            
            if(data.success == "success") {
                //successful transaction
                //console.log(JSON.parse(data.bank.bank).message);
                showDroidToast('Successful!');
                
                window.location = "{{ url('/withdrawal?uuid=Jiggy') }}";
                 
            } else {
                //transaction failed
                showDroidToast('Error, please retry!');
                
                
            }
        }).fail(function (jqXHR, textStatus, error) {
        //console.log("Post error: " + jqXHR.responseText);
         showDroidToast('Error, please retry!'+jqXHR.responseText);
         
         
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