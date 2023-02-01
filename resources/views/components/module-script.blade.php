{{--@auth--}}
{{--<span class="module_name" module="{{ $t->module_id }}">{{ $t->module_id }}</span>--}}
<script>
    let modules = {!! $modules !!},
        display_name = "{{ config('app.display_name') }}"

    let m = {}
    modules.forEach((module) => {
        //    转换成 key value
        m[module.id] = module.name

    })

    document.querySelectorAll('.module_name').forEach((node) => {
        let module = node.getAttribute('module')

        if (module == null || module === "") {
            node.innerText = display_name
        } else {
            console.log(module)
            node.innerText = m[module] ?? '模块'
        }
    })
</script>

{{--@endauth--}}
