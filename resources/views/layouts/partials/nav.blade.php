<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <!-- Left Side Of Navbar -->
            <ul class="nav navbar-nav">
                <li><a href="{{ route('users.search') }}">{{ __('app.search_your_family') }}</a></li>
                <li><a href="{{ route('birthdays.index') }}">{{ __('birthday.birthday') }}</a></li>
                <li><a href="{{ route('families.index') }}">{{ __('app.all_families') }}</a></li>
                @if (Auth::check() && is_system_admin(Auth::user()))
                    <li><a href="{{ route('users.index') }}">{{ __('app.users_list') }}</a></li>
                @endif            
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right d-flex align-items-center">
                <!-- Authentication Links -->
                <?php $mark = (preg_match('/\?/', url()->current())) ? '&' : '?';?>
                <li class="nav-item">
                    <select class="form-control" style="margin-top: 7px !important;" onchange="window.location.href=this.value;">
                        <option value="{{ url(url()->current() . $mark . 'lang=en') }}" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
                        <option value="{{ url(url()->current() . $mark . 'lang=ur') }}" {{ app()->getLocale() == 'ur' ? 'selected' : '' }}>اردو</option>
                    </select>
                </li>
                @if (Auth::guest())
                    <li class="nav-item"><a href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a href="{{ route('register') }}">Register</a></li>
                @else
                    <li class="dropdown nav-item">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>
            
                        <ul class="dropdown-menu" role="menu">
                            @if (is_system_admin(auth()->user()))
                                <li><a href="{{ route('backups.index') }}">{{ __('backup.list') }}</a></li>
                            @endif
                            <li><a href="{{ route('profile') }}">{{ __('app.my_profile') }}</a></li>
                            <li><a href="{{ route('password_change') }}">{{ __('auth.change_password') }}</a></li>
                            <li>
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                    Logout
                                </a>
            
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
            
        </div>
    </div>
</nav>