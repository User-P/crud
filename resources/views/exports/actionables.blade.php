<table>
    <thead>
        <tr>
            <th colspan="{{ count($headers) }}">
                <strong>Accionables {{ $sheetTitle ?? '' }}</strong>
            </th>
        </tr>
        <tr>
            <th colspan="{{ count($headers) }}">
                @foreach ($directivos ?? [] as $directivo)
                    {{ $directivo->siglas_directivo }}: {{ $directivo->nm_directivo }}
                    @if (!$loop->last) | @endif
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
                    <td>{{ $register[$header] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
