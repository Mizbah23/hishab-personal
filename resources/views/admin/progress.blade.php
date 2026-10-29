@extends('layouts.admin', ['title' => 'Build progress', 'heading' => 'Build progress'])

@section('content')
    <div class="page-heading-row">
        <div><span class="panel-kicker">Project roadmap</span><h2 class="page-title">See Hishab taking shape.</h2><p class="page-copy">A simple view of what is ready and what we will build next.</p></div>
        <div class="progress-summary"><strong>{{ count($completed) }}</strong><span>of {{ count($completed) + count($next) }} milestones complete</span></div>
    </div>

    <section class="content-card progress-card">
        <div class="progress-meter"><span style="width: {{ round((count($completed) / (count($completed) + count($next))) * 100) }}%"></span></div>
        <div class="progress-section"><div class="progress-section-heading"><span class="section-check completed-check">✓</span><div><span class="panel-kicker">Ready</span><h3>Completed milestones</h3></div><span class="card-count">{{ count($completed) }} done</span></div>
            @foreach ($completed as $item)<div class="milestone done"><span class="milestone-check">✓</span><span><strong>{{ $item['label'] }}</strong><small>{{ $item['detail'] }}</small></span></div>@endforeach
        </div>
        <div class="progress-section next-section"><div class="progress-section-heading"><span class="section-check next-check">→</span><div><span class="panel-kicker">Roadmap</span><h3>Coming next</h3></div><span class="card-count">{{ count($next) }} planned</span></div>
            @foreach ($next as $item)<div class="milestone"><span class="milestone-check"></span><span><strong>{{ $item['label'] }}</strong><small>{{ $item['detail'] }}</small></span></div>@endforeach
        </div>
    </section>
@endsection
