👇 #id{{ $id }}
@if ($from_username)
<b>From</b> <a href="https://bitclout.com/u/{{ $from_username }}">{{ $from_username }}</a>
@endif
<b>Action</b> <code>{{ $action }}</code>                
<b>Target</b> <a href="https://bitclout.com/u/{{ $target_username }}">{{ $target_username }}</a>
<b>Amount</b> <code>{{ $amount }}</code>
<b>Condition</b> <code>{{ $condition }}</code>
<b>Status</b> <code>{{ $status }}</code>
