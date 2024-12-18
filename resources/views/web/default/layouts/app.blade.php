<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));
@endphp

<head>
    @include('web.default.includes.metas')
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    <!-- General CSS File -->
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/toast/jquery.toast.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="/assets/default/css/app.css">

    @if($isRtl)
        <link rel="stylesheet" href="/assets/default/css/rtl-app.css">
    @endif

    @stack('styles_top')
    @stack('scripts_top')

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!}

        {!! getThemeFontsSettings() !!}

        {!! getThemeColorsSettings() !!}
          /* Style for the popup background */
       /* Popup Background */
#popupBackground {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

/* Popup Container */
#popupForm {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    padding: 20px;
    width: 90%;
    max-width: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 10000;
    text-align: center; /* Center content */
}

/* Popup Header Image */
#popupForm img {
    width: 100px;
    max-width: 350px;
    border-radius: 8px 8px 0 0;
    margin-bottom: 15px;
}

/* Popup Form Title */
#popupForm h2 {
    margin: 10px 0;
    font-size: 1.5em;
    color: #333;
}

/* Form Inputs */
#popupForm input, #popupForm textarea {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 5px;
}

/* Submit Button */
#popupForm button {
    padding: 10px 20px;
    background-color: #feb702;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

#popupForm button:hover {
    background-color: #00000;
}

/* Close Button */
#closePopup {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
    color: #555;
}
    </style>


    @if(!empty($generalSettings['preloading']) and $generalSettings['preloading'] == '1')
        @include('admin.includes.preloading')
    @endif
</head>

<body class="@if($isRtl) rtl @endif">

<div id="app" class="{{ (!empty($floatingBar) and $floatingBar->position == 'top' and $floatingBar->fixed) ? 'has-fixed-top-floating-bar' : '' }}">
    @if(!empty($floatingBar) and $floatingBar->position == 'top')
        @include('web.default.includes.floating_bar')
    @endif

    @if(!isset($appHeader))
        @include('web.default.includes.top_nav')
        @include('web.default.includes.navbar')
    @endif

    @if(!empty($justMobileApp))
        @include('web.default.includes.mobile_app_top_nav')
    @endif

    @yield('content')

    @if(!isset($appFooter))
        @include('web.default.includes.footer')
    @endif

    @include('web.default.includes.advertise_modal.index')

    @if(!empty($floatingBar) and $floatingBar->position == 'bottom')
        @include('web.default.includes.floating_bar')
    @endif
</div>
<!-- Template JS File -->
<script src="/assets/default/js/app.js"></script>
<script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
<script src="/assets/default/vendors/moment.min.js"></script>
<script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>
<script type="text/javascript" src="/assets/default/vendors/simplebar/simplebar.min.js"></script>

@if(empty($justMobileApp) and checkShowCookieSecurityDialog())
    @include('web.default.includes.cookie-security')
@endif


<script>
    var deleteAlertTitle = '{{ trans('public.are_you_sure') }}';
    var deleteAlertHint = '{{ trans('public.deleteAlertHint') }}';
    var deleteAlertConfirm = '{{ trans('public.deleteAlertConfirm') }}';
    var deleteAlertCancel = '{{ trans('public.cancel') }}';
    var deleteAlertSuccess = '{{ trans('public.success') }}';
    var deleteAlertFail = '{{ trans('public.fail') }}';
    var deleteAlertFailHint = '{{ trans('public.deleteAlertFailHint') }}';
    var deleteAlertSuccessHint = '{{ trans('public.deleteAlertSuccessHint') }}';
    var forbiddenRequestToastTitleLang = '{{ trans('public.forbidden_request_toast_lang') }}';
    var forbiddenRequestToastMsgLang = '{{ trans('public.forbidden_request_toast_msg_lang') }}';
</script>

@if(session()->has('toast'))
    <script>
        (function () {
            "use strict";

            $.toast({
                heading: '{{ session()->get('toast')['title'] ?? '' }}',
                text: '{{ session()->get('toast')['msg'] ?? '' }}',
                bgColor: '@if(session()->get('toast')['status'] == 'success') #43d477 @else #f63c3c @endif',
                textColor: 'white',
                hideAfter: 10000,
                position: 'bottom-right',
                icon: '{{ session()->get('toast')['status'] }}'
            });
        })(jQuery)
    </script>
@endif

@include('web.default.includes.purchase_notifications')

@stack('styles_bottom')
@stack('scripts_bottom')

<script src="/assets/default/js/parts/main.min.js"></script>

<script>
    @if(session()->has('registration_package_limited'))
    (function () {
        "use strict";

        handleLimitedAccountModal('{!! session()->get('registration_package_limited') !!}')
    })(jQuery)

    {{ session()->forget('registration_package_limited') }}
    @endif

    {!! !empty(getCustomCssAndJs('js')) ? getCustomCssAndJs('js') : '' !!}
</script>

<div id="popupBackground">
    <div id="popupForm">
        <button id="closePopup">&times;</button>
        <!-- Add an image at the top -->
        <img src="/store/1/pngtree-50-discount-speed-style-shape-png-image-png-image_7886991.png?timestamp=1734335821"  alt="Contact Us">
        <h2>{{trans('site.modal')}}</h2>
        <form action="/contact/store" method="post" >
                {{ csrf_field() }}
                <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name')  is-invalid @enderror" placeholder="{{ trans('public.name') }}"/>
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
             <input type="text" name="email" value="{{ old('email') }}" class="form-control @error('email')  is-invalid @enderror" placeholder="{{ trans('public.email') }}"/>
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone')  is-invalid @enderror" placeholder="{{ trans('site.phone_number') }}"/>
                            @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
             <textarea type="text" name="message" value="{{ old('message') }}" class="form-control @error('message')  is-invalid @enderror" placeholder"{{ trans('site.message') }}">{{ trans('site.message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
            <button id="submitForm"  type="submit" class="btn btn-primary mt-20">{{ trans('site.send_message') }}</button>
        </form>
    </div>
</div>

<script>
// Function to check if the user is authenticated
function isGuestUser() {
    // Check for the presence of an auth token
    var AuthUser = "{{{ (Auth::user()) ? true : false }}}";
    return !AuthUser;
}

// Function to get a cookie by name
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Function to show the popup
function showPopup() {
    document.getElementById("popupBackground").style.display = "block";
}

// Function to hide the popup
function hidePopup() {
    document.getElementById("popupBackground").style.display = "none";
}

// Function to start the popup loop
function startPopupLoop() {
    if (isGuestUser() && !sessionStorage.getItem('formSubmitted')) {
        console.log(sessionStorage.getItem('formSubmitted'));
        showPopup();
        setTimeout(startPopupLoop, 25000); // Replay every 10 seconds
    }
}

// Add event listeners when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", function () {
    if (isGuestUser()) {
        // Start the popup loop after an initial delay
        setTimeout(startPopupLoop, 25000);

        // Add event listener to the submit button
        const submitButton = document.getElementById("submitForm");
        if (submitButton) {
            submitButton.onclick = function () {
                sessionStorage.setItem('formSubmitted', 'true');
                hidePopup();
            };
        }

        // Add event listener to the close button
        const closeButton = document.getElementById("closePopup");
        if (closeButton) {
            closeButton.onclick = function () {
                hidePopup();
            };
        }
    }
});
</script>



</body>
</html>
