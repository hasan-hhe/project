<button class="btn-animation" data-form="{{ $fromId }}">
    <span>{{ $text }}</span>
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
        viewBox="0 0 24 24">
        <path
            d="M0 11c2.761.575 6.312 1.688 9 3.438 3.157-4.23 8.828-8.187 15-11.438-5.861 5.775-10.711 12.328-14 18.917-2.651-3.766-5.547-7.271-10-10.917z" />
    </svg>
</button>

@push('styles')
    <style>
        .btn-animation {
            width: 150px;
            height: 50px;
            background: none;
            border: 4px solid #1a2035;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 100ms;
        }

        .btn-animation span,
        .btn-animation svg {
            position: absolute;
            color: #002651;
            fill: transparent;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
        }

        .btn-animation:focus {
            outline: none;
            border: 2px solid #1e2a78;
            width: 50px;
            border-left: 4px solid #1e2a78;
            border-bottom: 4px solid #1e2a78;
            animation: spin 2s 500ms forwards;
        }

        .btn-animation:focus span {
            color: transparent;
        }

        .btn-animation:focus svg {
            animation: check 500ms 2300ms forwards;
        }

        @keyframes spin {
            80% {
                border: 4px solid transparent;
                border-left: 4px solid #303a52;
            }

            100% {
                transform: rotate(1080deg);
                border: 4px solid #303a52;
            }
        }

        @keyframes check {
            to {
                fill: #17b978;
            }
        }

        @keyframes circle {
            to {
                border: 4px solid #303a52;
                color: blue;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.querySelector('.btn-animation span').addEventListener('click', function () {
            let form = document.querySelector('#' + this.dataset.form);

            setTimeout(() => {
                form.submit();
            }, 2000);
        });
    </script>
@endpush