<div class="form-group">
    <label for="sessions">{{ $typeCheckboxTextArabic }}</label>
    <button type="button" class="btn btn-outline-primary selectAll" style="float: right;">الكل</button>
    <button type="button" class="btn btn-outline-danger disAll mx-1" style="float: right;">إلغاء</button>
    <input id="search" class="form-control mt-3 search" type="text" placeholder="بحث">
    <div id="checklist" style="overflow-y: scroll; height:300px">
        @foreach ($items as $item)
            <input @if (in_array($item, $checkedItems)) checked @endif value="{{ $item->$value }}"
                name="{{ $item->$name }}" type="checkbox" id="checkbox-{{ $item->$id }}" />
            <label for="checkbox-{{ $item->$id }}">{{ $item->$label }}</label>
        @endforeach
    </div>
</div>

@push('styles')
    <style>
        /* From Uiverse.io by della11 */
        #checklist {
            --background: #303952;
            --text: #5d6474;
            --check: #cc29f0;
            --disabled: #d3c8de;
            --width: 100%;
            --height: 180px;
            --border-radius: 10px;
            /* background: var(--background); */
            width: var(--width);
            height: var(--height);
            border-radius: var(--border-radius);
            position: relative;
            box-shadow: 0 10px 30px rgba(65, 72, 86, 0.05);
            padding: 30px 50px;
            display: grid;
            grid-template-columns: 30px auto;
            /* align-items: left;
            justify-content: center; */
        }

        #checklist label {
            color: var(--text);
            position: relative;
            cursor: pointer;
            display: grid;
            align-items: center;
            width: fit-content;
            transition: color 0.3s ease;
            margin-left: 20px;
        }

        #checklist label::before,
        #checklist label::after {
            content: "";
            position: absolute;
        }

        #checklist label::before {
            height: 2px;
            width: 8px;
            right: -27px;
            background: var(--check);
            border-radius: 2px;
            transition: background 0.3s ease;
        }

        #checklist label:after {
            height: 4px;
            width: 4px;
            top: 8px;
            right: -25px;
            border-radius: 50%;
        }

        #checklist input[type="checkbox"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            position: relative;
            height: 15px;
            width: 15px;
            outline: none;
            border: 1px black solid;
            margin: 19px 0px 0 15px;
            cursor: pointer;
            border-radius: 5px;
            display: grid;
            align-items: center;
            margin-left: 20px;
        }

        #checklist input[type="checkbox"]::before,
        #checklist input[type="checkbox"]::after {
            content: "";
            position: absolute;
            height: 2px;
            top: auto;
            background: var(--check);
            border-radius: 2px;
        }

        #checklist input[type="checkbox"]::before {
            width: 0px;
            left: 60%;
            transform-origin: left top;
        }

        #checklist input[type="checkbox"]::after {
            width: 0px;
            right: 40%;
            transform-origin: right top;
        }

        #checklist input[type="checkbox"]:checked::before {
            animation: check-01 0.4s ease forwards;
        }

        #checklist input[type="checkbox"]:checked::after {
            animation: check-02 0.4s ease forwards;
        }

        #checklist input[type="checkbox"]:checked+label {
            color: var(--disabled);
            animation: move 0.3s ease 0.1s forwards;
        }

        #checklist input[type="checkbox"]:checked+label::before {
            background: var(--disabled);
            animation: slice 0.4s ease forwards;
        }

        #checklist input[type="checkbox"]:checked+label::after {
            animation: firework 0.5s ease forwards 0.1s;
        }

        @keyframes move {
            50% {
                padding-right: 8px;
                padding-left: 0px;
            }

            100% {
                padding-left: 4px;
            }
        }

        @keyframes slice {
            60% {
                width: 100%;
                right: 4px;
            }

            100% {
                width: 100%;
                right: -2px;
                padding-right: 0;
            }
        }

        @keyframes check-01 {
            0% {
                width: 4px;
                top: auto;
                transform: rotate(0);
            }

            50% {
                width: 0px;
                top: auto;
                transform: rotate(0);
            }

            51% {
                width: 0px;
                top: 10px;
                transform: rotate(-135deg);
            }

            100% {
                width: 9px;
                top: 10px;
                transform: rotate(-135deg);
            }
        }

        @keyframes check-02 {
            0% {
                width: 4px;
                top: auto;
                transform: rotate(0);
            }

            50% {
                width: 0px;
                top: auto;
                transform: rotate(0);
            }

            51% {
                width: 0px;
                top: 10px;
                transform: rotate(120deg);
            }

            100% {
                width: 17px;
                top: 10px;
                transform: rotate(120deg);
            }
        }

        @keyframes firework {
            0% {
                opacity: 1;
                box-shadow: 0 0 0 -2px #4f29f0, 0 0 0 -2px #4f29f0, 0 0 0 -2px #4f29f0,
                    0 0 0 -2px #4f29f0, 0 0 0 -2px #4f29f0, 0 0 0 -2px #4f29f0;
            }

            30% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                box-shadow: 0 -15px 0 0px #4f29f0, 14px -8px 0 0px #4f29f0,
                    14px 8px 0 0px #4f29f0, 0 15px 0 0px #4f29f0, -14px 8px 0 0px #4f29f0,
                    -14px -8px 0 0px #4f29f0;
            }
        }
    </style>
@endpush
