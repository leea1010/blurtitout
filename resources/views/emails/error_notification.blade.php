<h3>以下の通り、システム処理中に想定外の停止、またはエラーが検知されました。</h3>
<br>
<p><strong>■ 発生日時：</strong>{{ $datetime }}</p>
<p><strong>■ 対象処理：</strong>{{ $process }}</p>
<p><strong>■ 処理件数：</strong>{{ $total }}件中 {{ $failed }}件で失敗</p>
<p><strong>■ エラー内容：</strong>{{ $error }}</p>
<p><strong>■ ログURL　：</strong><a href="{{ $logUrl }}">{{ $logUrl }}</a></p>