@props(['proposal'])

@php
    use App\Models\Proposal;
    
    $stages = ['draft', 'feedback', 'refinement', 'voting', 'closed'];
    $stageOrder = array_flip($stages);
    $currentStageOrder = $stageOrder[$proposal->current_stage] ?? 0;
    
    // Handle archived/withdrawn as equivalent to closed for display
    if (in_array($proposal->current_stage, ['archived', 'withdrawn'])) {
        $currentStageOrder = $stageOrder['closed'];
    }
@endphp

<div class="flex items-center justify-between py-3">
    @foreach($stages as $index => $stage)
        @php
            $stageConfig = Proposal::STAGES[$stage] ?? [];
            $isComplete = $index < $currentStageOrder;
            $isCurrent = $index === $currentStageOrder;
            $isPending = $index > $currentStageOrder;
        @endphp
        
        <div class="flex flex-col items-center {{ $index === 0 ? '' : 'flex-1' }}">
            @if($index > 0)
                {{-- Connector Line --}}
                <div class="w-full h-0.5 mb-2 {{ $isComplete ? 'bg-green-500' : 'bg-gray-200' }}"></div>
            @endif
            
            <div class="flex flex-col items-center">
                {{-- Stage Circle --}}
                <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors
                            {{ $isCurrent ? 'bg-' . ($stageConfig['color'] ?? 'gray') . '-500 text-white ring-4 ring-' . ($stageConfig['color'] ?? 'gray') . '-100' : '' }}
                            {{ $isComplete ? 'bg-green-500 text-white' : '' }}
                            {{ $isPending ? 'bg-gray-200 text-gray-400' : '' }}">
                    @if($isComplete)
                        <x-heroicon-s-check class="w-4 h-4" />
                    @else
                        <x-dynamic-component :component="'heroicon-o-' . ($stageConfig['icon'] ?? 'question-mark-circle')" 
                                             class="w-4 h-4" />
                    @endif
                </div>
                
                {{-- Stage Label --}}
                <span class="text-xs mt-1 font-medium
                             {{ $isCurrent ? 'text-' . ($stageConfig['color'] ?? 'gray') . '-600' : '' }}
                             {{ $isComplete ? 'text-green-600' : '' }}
                             {{ $isPending ? 'text-gray-400' : '' }}">
                    {{ $stageConfig['name'] ?? ucfirst($stage) }}
                </span>
            </div>
        </div>
    @endforeach
</div>
