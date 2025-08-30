@props(['title' => null, 'navbarHeading' => null])
<x-layouts.app.sidebar :title="$title ?? null" :navbarHeading="$navbarHeading ?? null">
    <flux:main>
        {{ $slot }}

        {{--toastr--}}
        <script>
            if (!window.notifyListenerAdded) {
                window.addEventListener('notify', event => {
                    let type = event.detail.type;
                    let message = event.detail.message;

                    toastr.options = {
                        "closeButton": true,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "timeOut": "4000"
                    };

                    switch (type) {
                        case 'success':
                            toastr.success(message);
                            break;
                        case 'error':
                            toastr.error(message);
                            break;
                        case 'warning':
                            toastr.warning(message);
                            break;
                        default:
                            toastr.info(message);
                    }
                });

                window.notifyListenerAdded = true;
            }
        </script>

    </flux:main>
</x-layouts.app.sidebar>
