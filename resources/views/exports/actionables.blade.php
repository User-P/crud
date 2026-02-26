<table>
    <thead>
        <tr>
            <th>
                <strong>Accionables </strong>
            </th>
        </tr>

        <tr>
            <th>
                @foreach ($directivos as $directivo)
                {{ $directivo->siglas_directivo }} : {{ $directivo->nm_directivo }}
                @endforeach
            </th>
        </tr>
        <tr>
            @foreach ($headers as $header)
            <th>{{ $header }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach ($registers as $register)
        <tr>
            @foreach ($headers as $header)
            <td>
                {{ $register[$header] ?? '' }}
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
