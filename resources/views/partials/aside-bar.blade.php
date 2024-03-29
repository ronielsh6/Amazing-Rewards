<!-- HERE BEGINS LATERAL BAR -->
<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3   bg-gradient-dark"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
           aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href=" https://demos.creative-tim.com/material-dashboard/pages/dashboard "
           target="_blank">
            <img src="{{ asset('assets/img/app_icon.png') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold text-white">Amazing Rewards</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            {{-- <li class="nav-item">
              <a class="nav-link text-white " href="./dashboard.html">
                  <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="material-icons opacity-10">dashboard</i>
                  </div>
                <span class="nav-link-text ms-1">Dashboard</span>
              </a>
            </li> --}}
            <li class="nav-item">
                <a class="nav-link text-white @if(Route::is('home')) active @endif" href="{{ route('home') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">table_view</i>
                    </div>
                    <span class="nav-link-text ms-1">Allow List</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white @if(Route::is('blacklist')) active @endif"
                   href="{{ route('blacklist') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">block</i>
                    </div>
                    <span class="nav-link-text ms-1">Deny List</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white @if(Route::is('showGiftCards')) active @endif"
                   href="{{ route('showGiftCards') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">redeem</i>
                    </div>
                    <span class="nav-link-text ms-1">Gift Cards</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white @if(Route::is('campaigns')) active @endif"
                   href="{{ route('campaigns') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">schedule</i>
                    </div>
                    <span class="nav-link-text ms-1">Campaigns</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white @if(Route::is('blockedLogs')) active @endif"
                   href="{{ route('blockedLogs') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">history</i>
                    </div>
                    <span class="nav-link-text ms-1">Logs for blocked users</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white @if(Route::is('promoCodes')) active @endif"
                   href="{{ route('promoCodes') }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">confirmation_number</i>
                    </div>
                    <span class="nav-link-text ms-1">Promo Code</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
<!-- HERE END LATERAL BAR -->
