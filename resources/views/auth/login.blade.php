@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center h-screen" style="height: 60vh">
        <div class="text-center">
            <span style="font-size: 10rem">
                <i class="bi bi-person-circle" id="main-icon"></i>

            </span>

            <h2 id="form-title">注册 或 登录</h2>

            <form id="main-form" method="POST" onsubmit="return canSubmit()">
                @csrf

                <div class="form-group">
                    <input type="email" name="email" id="email" class="form-control mb-3 text-center" placeholder="邮箱"
                           aria-label="邮箱" required autofocus>
                </div>

                <div id="suffix-form"></div>
            </form>

            <br/>

            <a class="link" href="{{ route('password.request') }}">
                {{ __('Forgot Your Password?') }}
            </a>


        </div>
    </div>


    <div class="d-none">

        <div id="password-input">
            <div class="form-group mt-2">
                <input type="password" id="password" name="password"
                       class="form-control rounded-right text-center @error('password') is-invalid @enderror" required
                       placeholder="密码" aria-label="密码">
                @error('password')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                @enderror
            </div>
        </div>


        <div class="form-group mt-2" id="password-confirm-input">
            <label for="password-confirm">确认密码</label>
            <input type="password" id="password-confirm" name="password_confirmation"
                   class="form-control rounded-right" required autocomplete="new-password"
                   placeholder="再次输入您的密码">
        </div>

        <div id="remember-form">
            <input class="form-check-input" type="hidden" id="remember" name="remember" value="1">
        </div>

        <small id="tip" class="d-block"></small>

        <div class="mt-1" id="tos">如果您继续，则代表您已经阅读并同意 <a
                href="https://www.laecloud.com/tos/"
                target="_blank"
                class="text-decoration-underline">服务条款</a>
        </div>

        <button class="btn btn-primary btn-block mt-2" type="submit" id="login-btn">
            继续
        </button>
    </div>


    <script>
        const login = "{{ route('login') }}"
        const register = "{{ route('register') }}"

        const mainIcon = document.getElementById('main-icon')
        const email = document.getElementById('email');
        const title = document.getElementById('form-title');
        const formSuffix = document.getElementById('suffix-form')
        const rememberForm = document.getElementById('remember-form')
        const passwordInput = document.getElementById('password-input')
        const passwordConfirmInput = document.getElementById('password-confirm-input')
        const loginBtn = document.getElementById('login-btn')
        const nameInput = document.getElementById('name')
        const mainForm = document.getElementById('main-form')
        const tos = document.getElementById('tos')
        const tip = document.getElementById('tip')

        @error('password')
            title.innerText = "注册莱云"
        formSuffix.appendChild(rememberForm)


        @enderror

            @error('email')
            title.innerText = "密码错误"
        email.value = "{{ old('email') }}"
        formSuffix.appendChild(passwordInput)
        formSuffix.appendChild(rememberForm)
        formSuffix.appendChild(tos)
        formSuffix.appendChild(loginBtn)
        loginBtn.innerText = '登录'
        @enderror

        let canSubmit = function () {
            return (email.value !== '' && passwordInput.value !== '')
        }

        const validateUrl = "{{ route('login.exists-if-user') }}"

        email.onchange = function (ele) {
            const target = ele.target

            if (email.value === '') {
                title.innerText = "输入邮箱"

                formSuffix.innerHTML = ''


                mainIcon.classList.remove(...mainIcon.classList)
                mainIcon.classList.add('bi', 'bi-person-circle')

                return
            }

            formSuffix.innerHTML = ''
            formSuffix.appendChild(passwordInput)

            axios.post(validateUrl, {
                email: target.value
            })
                .then(function (res) {
                    mainForm.action = login

                    mainIcon.classList.remove(...mainIcon.classList)
                    mainIcon.classList.add('bi', 'bi-person-check')

                    title.innerText = "欢迎, " + res.data.name

                    formSuffix.appendChild(passwordInput)
                    formSuffix.appendChild(rememberForm)
                    formSuffix.appendChild(tos)
                    formSuffix.appendChild(loginBtn)
                    loginBtn.innerText = '登录'


                })
                .catch(function () {
                    mainForm.action = register

                    title.innerText = "注册莱云"

                    mainIcon.classList.remove(...mainIcon.classList)
                    mainIcon.classList.add('bi', 'bi-person-plus')

                    formSuffix.appendChild(passwordInput)
                    formSuffix.appendChild(tos)
                    formSuffix.appendChild(tip)

                    formSuffix.appendChild(loginBtn)

                    tip.innerText = '当您注册后，我们将为您分配随机用户名。'

                    loginBtn.innerText = '注册'
                });
        }


    </script>

@endsection
