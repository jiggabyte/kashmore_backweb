@extends('layouts.templater')

@section('homer')
    
  <section class="mbr-section form1 cid-s7VkVNOM2l" id="form1-0">




    <div class="container">
        <div class="row justify-content-center">
            <div class="title col-12 col-lg-8">
                <h2 class="mbr-section-title align-center pb-3 mbr-fonts-style display-2">
                    Bank Details
                </h2>
                <h3 class="mbr-section-subtitle align-center mbr-light pb-3 mbr-fonts-style display-5">
                    Add your bank details to Withdraw?
                </h3>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="media-container-column col-lg-8" data-form-type="formoid">
                <div data-form-alert="" hidden="">
                    Thanks for filling out the form!
                </div>

                <form class="mbr-form" action="#" method="post" data-form-title="Mobirise Form">
                    <input type="hidden" name="email" data-form-email="true" value="c0JaVP90copFPuOPPfVUHMwWk16uE/cwrz77iJ2M71Mp15jKEl+WlfniLz0itPkTyrA6FV0b0xziNco1gKrg8b/vM3AJwlA+XJcjzf2pcPObIa/TdKT71tSKWDZh1/IY" data-form-field="Email">
                    <div class="row row-sm-offset">
                        <div class="col-md-4 multi-horizontal" data-for="bank">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="bank">Bank</label>
                                <input type="text" class="form-control" name="bank" data-form-field="Bank" required="" id="bank">
                            </div>
                        </div>
                        <div class="col-md-4 multi-horizontal" data-for="name">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="name">Name</label>
                                <input type="text" class="form-control" name="name" data-form-field="Name" required="" id="name">
                            </div>
                        </div>
                        <div class="col-md-4 multi-horizontal" data-for="account">
                            <div class="form-group">
                                <label class="form-control-label mbr-fonts-style display-7" for="account">Account</label>
                                <input type="text" class="form-control" name="account" data-form-field="Account" id="account">
                            </div>
                        </div>
                    </div>

                    <span class="input-group-btn">
                            <button type="submit" id="payBnk" class="btn btn-primary btn-form display-4" style="width:80%;font-size:1.2em!important;">Withdraw</button>
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

document.querySelector('#payBnk').onclick = (e) => {
           e.preventDefault();
           var montant = window.localStorage.getItem('montant');
           var bank = document.querySelector('#bank').value;
           var name = document.querySelector('#name').value;
           var account = document.querySelector('#account').value;
           alert('Request Sent');


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