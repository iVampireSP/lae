<table class="table table-hover">
    <thead>
    <th>年 / 月</th>

    @for ($i = 1; $i < 13; $i++)
        <th>{{ $i }} 月</th>
    @endfor
    </thead>
    <tbody>

    @foreach ($years as $year => $months)
        <tr>
            <td>{{ $year }}</td>
            @for ($i = 1; $i < 13; $i++)

                <td @if (($months[$i]['balance'] ?? 0) > 0) class="text-danger" @endif>{{ round(($months[$i]['balance'] ?? 0), 2) ?? 0 }}
                    元
                </td>

            @endfor
        </tr>
    @endforeach
    </tbody>
</table>
