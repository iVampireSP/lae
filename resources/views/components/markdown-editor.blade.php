<div>
    <link rel="stylesheet" href="{{ asset('vendor/editor.md/css/editormd.min.css') }}"/>

    @php($rand = rand(0, 1000))
    <div id="md_{{$rand}}_{{ $name }}" class="mt-3">

        <textarea id="md_{{$rand}}_{{ $name }}_textarea" name="{{ $name }}" style="display:none;" placeholder="{{ $placeholder }}"
                  aria-label="{{ $placeholder }}">{{ $value ?? old('content') }}</textarea>

    </div>


    <script src="{{ asset('vendor/editor.md/lib/zepto.min.js') }}"></script>
    <script src="{{ asset('vendor/editor.md/lib/marked.min.js') }}"></script>
    <script src="{{ asset('vendor/editor.md/lib/prettify.min.js') }}"></script>
    <script src="{{ asset('vendor/editor.md/lib/underscore.min.js') }}"></script>
    <script src="{{ asset('vendor/editor.md/lib/flowchart.min.js') }}"></script>
    <script src="{{ asset('vendor/editor.md/lib/jquery.flowchart.min.js')}}"></script>
    <script src="{{ asset('vendor/editor.md/editormd.min.js') }}"></script>
    <script>
        @php($editor = 'editor_' . $rand)
        let {{ $editor }};

        let darkTheme = false;

        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            darkTheme = true;
        }

        {{ $editor }} = editormd("md_{{$rand}}_{{ $name }}", {
            width: "100%",
            height: 740,
            path: '{{ asset('vendor/editor.md/lib') }}/',
            theme: darkTheme ? 'dark' : 'default',
            previewTheme: darkTheme ? 'dark' : 'default',
            editorTheme: darkTheme ? 'pastel-on-dark' : 'default',
            markdown: document.getElementById("md_{{$rand}}_{{ $name }}_textarea").value,
            codeFold: true,
            saveHTMLToTextarea: false,
            searchReplace: true,
            htmlDecode: "style,script,iframe|on*",
            imageUpload: false,
            imageFormats: ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
            imageUploadURL: "./php/upload.php",
            placeholder: '{{ nl2br($placeholder) }}',
        });
    </script>

</div>
