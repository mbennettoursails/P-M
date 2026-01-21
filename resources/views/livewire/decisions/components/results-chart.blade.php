<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('decisions.results.title') }}</h2>

    @if(!$results['visible'])
        {{-- Results Hidden --}}
        <div class="text-center py-8">
            <x-heroicon-o-eye-slash class="w-12 h-12 mx-auto mb-3 text-gray-300" />
            <p class="text-gray-500">{{ __('decisions.results.hidden') }}</p>
            
            {{-- Basic Stats --}}
            <div class="mt-6 flex justify-center gap-8 text-sm">
                <div>
                    <p class="text-gray-400">{{ __('decisions.results.total_votes') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $results['total_votes'] }}</p>
                </div>
                <div>
                    <p class="text-gray-400">{{ __('decisions.labels.participants') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $results['voter_count'] }}</p>
                </div>
            </div>
        </div>
    @else
        {{-- Visible Results --}}
        <div class="space-y-6">
            {{-- Quorum Status --}}
            <div class="flex items-center justify-between p-4 rounded-lg 
                        {{ $results['quorum_reached'] ? 'bg-green-50' : 'bg-yellow-50' }}">
                <div class="flex items-center">
                    @if($results['quorum_reached'])
                        <x-heroicon-o-check-circle class="w-6 h-6 text-green-500 mr-3" />
                        <span class="font-medium text-green-700">{{ __('decisions.results.quorum_reached') }}</span>
                    @else
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-yellow-500 mr-3" />
                        <span class="font-medium text-yellow-700">{{ __('decisions.results.quorum_not_reached') }}</span>
                    @endif
                </div>
                <span class="text-sm {{ $results['quorum_reached'] ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $results['total_votes'] }}/{{ $results['voter_count'] }} 
                    ({{ $results['vote_percentage'] }}% / {{ $results['quorum_percentage'] }}%)
                </span>
            </div>

            {{-- Vote Distribution Bars --}}
            <div class="space-y-4">
                @foreach($results['distribution'] as $option => $data)
                    @php
                        $optionConfig = $voteOptions[$option] ?? ['label' => $option, 'color' => 'gray'];
                        $colors = $tailwindColors[$optionConfig['color']] ?? $tailwindColors['gray'];
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">
                                {{ app()->getLocale() === 'ja' ? $optionConfig['label'] : $optionConfig['label_en'] }}
                            </span>
                            <span class="text-sm {{ $colors['text'] }}">
                                {{ $data['count'] }} ({{ $data['percentage'] }}%)
                            </span>
                        </div>
                        <div class="w-full h-4 {{ $colors['bg-light'] }} rounded-full overflow-hidden">
                            <div class="h-full {{ $colors['bg'] }} rounded-full transition-all duration-500"
                                 style="width: {{ $data['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Outcome (if closed) --}}
            @if(isset($results['outcome']))
                @php
                    $outcomeConfig = $results['outcome_config'] ?? ['name_ja' => $results['outcome'], 'color' => 'gray', 'icon' => 'question-mark-circle'];
                @endphp
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center justify-center p-4 rounded-lg bg-{{ $outcomeConfig['color'] }}-50">
                        <x-dynamic-component :component="'heroicon-o-' . $outcomeConfig['icon']" 
                                             class="w-8 h-8 text-{{ $outcomeConfig['color'] }}-500 mr-3" />
                        <div class="text-center">
                            <p class="text-sm text-{{ $outcomeConfig['color'] }}-600">{{ __('decisions.results.outcome') }}</p>
                            <p class="text-2xl font-bold text-{{ $outcomeConfig['color'] }}-700">
                                {{ $outcomeConfig['name_ja'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Summary Stats --}}
            <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-100">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $results['total_votes'] }}</p>
                    <p class="text-xs text-gray-500">{{ __('decisions.results.total_votes') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $results['voter_count'] }}</p>
                    <p class="text-xs text-gray-500">{{ __('decisions.labels.participants') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold {{ $results['quorum_reached'] ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ $results['vote_percentage'] }}%
                    </p>
                    <p class="text-xs text-gray-500">投票率</p>
                </div>
            </div>
        </div>
    @endif
</div>
