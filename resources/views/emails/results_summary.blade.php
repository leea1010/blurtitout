<h3>本日のシステム処理が完了しましたので、以下に結果をご報告いたします。</h3>

<p><strong>■ 実行日：</strong> {{ $executionDate }}</p>
<p><strong>■ 処理対象：</strong> レインズ／バッチ処理</p>
<p><strong>■ 開始時刻：</strong> {{ $startTime }} / <strong>終了時刻：</strong> {{ $endTime }}</p>
<hr>
<p><strong>■ 処理件数：</strong> {{ $recordCount }}件</p>
<p><strong>■ 成功件数：</strong> {{ $successCount }}件</p>
<p><strong>■ 失敗件数：</strong> {{ $errorCount }}件</p>
<hr>
<p><strong>■ 検索タイプ別統計：</strong></p>
@if(isset($searchTypeStats))
<ul>
    @foreach($searchTypeStats as $searchType => $count)
    <li>{{ $searchType }}: {{ $count }}件</li>
    @endforeach
</ul>
@endif
<hr>
<p><strong>■ 停止理由：</strong> {{ $stopReason }}</p>
<p><strong>■ ログURL：</strong> <a href="{{ $logUrl }}">{{ $logUrl }}</a></p>