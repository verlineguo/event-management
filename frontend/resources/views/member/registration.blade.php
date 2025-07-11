@extends('member.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('guest/css/event.css') }}">
@endsection

@section('content')
    <div class="registration-container">
        <!-- Progress Bar -->
        <div class="progress-bar-container">
            <div class="progress-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-title">Pilih Sesi</div>
                    <div class="step-connector"></div>
                </div>
                <div class="step inactive">
                    <div class="step-number">2</div>
                    <div class="step-title">Pembayaran</div>
                    <div class="step-connector"></div>
                </div>
                <div class="step inactive">
                    <div class="step-number">3</div>
                    <div class="step-title">Konfirmasi</div>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 33.33%"></div>
            </div>
        </div>

        <!-- Event Summary -->
        <div class="event-summary">
            @if (isset($event['poster']) && !empty($event['poster']))
                <div style="text-align:center; margin-bottom:20px;">
                    <img src="{{ asset('storage/' . $event['poster']) }}" alt="Poster Event"
                        style="max-width:400px; width:100%; border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.08); cursor:pointer"
                        onclick="showPosterModal(this.src)">

                    <!-- Modal for enlarged poster -->
                    <div id="posterModal"
                        style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.7); align-items:center; justify-content:center;">
                        <span onclick="closePosterModal()"
                            style="position:absolute;top:30px;right:40px;font-size:40px;color:white;cursor:pointer;z-index:10001;">&times;</span>
                        <img id="posterModalImg" src=""
                            style="max-width:90vw; max-height:90vh; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,0.4);">
                    </div>
                </div>
            @endif
            <div class="event-title">{{ $event['name'] ?? 'Event Name' }}</div>
            <div class="event-meta">
                <i class="fas fa-calendar-alt"></i>
                <span>{{ $event['date_range'] ?? 'Date Range' }}</span>
            </div>
            <div class="event-meta">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ $event['display_location'] ?? 'Location' }}</span>
            </div>
        </div>

        @if (session('draft_saved'))
            <div class="draft-indicator">
                <i class="fas fa-save"></i>
                Draft registrasi berhasil disimpan! Anda dapat melanjutkan nanti.
            </div>
        @endif

        @if (isset($draft) && $draft)
            <div class="draft-indicator">
                <i class="fas fa-edit"></i>
                Anda sedang melanjutkan draft registrasi yang tersimpan.
            </div>
        @endif

        <form id="registrationForm" method="POST" action="{{ route('member.events.store-registration', $event['_id']) }}">
            @csrf

            <div class="registration-card">
                <!-- Session Selection -->
                <div class="session-selection">
                    <h3 class="section-title">
                        <i class="fas fa-layer-group"></i>
                        Pilih Sesi Event
                    </h3>

                    @if (isset($event['sessions']) && count($event['sessions']) > 1)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Anda dapat memilih satu atau beberapa sesi sesuai kebutuhan. Biaya akan dihitung berdasarkan
                            sesi yang dipilih.
                        </div>
                    @endif

                    <div class="sessions-list">
                        @if (isset($event['sessions']) && count($event['sessions']) > 0)
                            @foreach ($event['sessions'] as $index => $session)
                                @php
                                    $availableSlots = $session['available_slots'] ?? 0;
                                    $maxParticipants = $session['max_participants'] ?? 0;
                                    $registeredCount = $maxParticipants - $availableSlots;
                                    $capacityPercentage =
                                        $maxParticipants > 0 ? ($registeredCount / $maxParticipants) * 100 : 0;
                                    $isFull = $availableSlots <= 0;
                                    $sessionFee = $session['session_fee'] ?? 0;

                                    $capacityClass = 'low';
                                    if ($capacityPercentage >= 100) {
                                        $capacityClass = 'full';
                                    } elseif ($capacityPercentage >= 80) {
                                        $capacityClass = 'high';
                                    } elseif ($capacityPercentage >= 50) {
                                        $capacityClass = 'medium';
                                    }

                                    $isSelected =
                                        isset($draft['selected_sessions']) &&
                                        in_array($session['_id'], $draft['selected_sessions']);
                                @endphp

                                <div class="session-card {{ $isFull ? 'full' : '' }} {{ $isSelected ? 'selected' : '' }}"
                                    data-session-id="{{ $session['_id'] }}" data-session-fee="{{ $sessionFee }}"
                                    onclick="{{ $isFull ? '' : 'toggleSession(this)' }}">
                                    <div class="session-header">
                                        <div
                                            style="display: flex; align-items: flex-start; justify-content: space-between; width: 100%;">
                                            <div style="display: flex; align-items: flex-start;">
                                                @if (!$isFull)
                                                    <input type="checkbox" name="selected_sessions[]"
                                                        value="{{ $session['_id'] }}" class="session-checkbox"
                                                        {{ $isSelected ? 'checked' : '' }} onchange="updateSelection()">
                                                @endif
                                                <div class="session-info">
                                                    <h4>
                                                        Session {{ $session['session_order'] ?? $index + 1 }}
                                                        @if ($isFull)
                                                            <span class="badge badge-danger ml-2">PENUH</span>
                                                        @endif
                                                    </h4>
                                                    @if (isset($session['title']) && $session['title'])
                                                        <p style="margin: 5px 0; font-weight: 500;">{{ $session['title'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <!-- Session Fee -->
                                            <div class="session-fee">
                                                @if ($sessionFee > 0)
                                                    <span class="fee-amount">Rp {{ number_format($sessionFee) }}</span>
                                                @else
                                                    <span class="fee-free">GRATIS</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="session-details">
                                        <div class="session-detail">
                                            <i class="fas fa-calendar"></i>
                                            <span>{{ \Carbon\Carbon::parse($session['date'])->format('l, d M Y') }}</span>
                                        </div>
                                        <div class="session-detail">
                                            <i class="fas fa-clock"></i>
                                            <span>{{ $session['start_time'] ?? 'TBA' }} -
                                                {{ $session['end_time'] ?? 'TBA' }}</span>
                                        </div>
                                        <div class="session-detail">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $session['location'] ?? 'Location TBA' }}</span>
                                        </div>
                                        <div class="session-detail">
                                            <i class="fas fa-microphone"></i>
                                            <span>{{ $session['speaker'] ?? 'Speaker TBA' }}</span>
                                        </div>
                                    </div>

                                    @if (isset($session['description']) && $session['description'])
                                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                                            <p style="margin: 0; color: #666; font-size: 14px;">
                                                {{ $session['description'] }}</p>
                                        </div>
                                    @endif

                                    <!-- Capacity Information -->
                                    <div class="capacity-info">
                                        <span style="font-size: 14px; font-weight: 500;">
                                            {{ $registeredCount }}/{{ $maxParticipants }} peserta
                                        </span>
                                        <div class="capacity-bar">
                                            <div class="capacity-fill {{ $capacityClass }}"
                                                style="width: {{ min(100, $capacityPercentage) }}%"></div>
                                        </div>
                                        <span style="font-size: 14px; color: #666;">
                                            {{ $availableSlots }} slot tersisa
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Tidak ada sesi yang tersedia untuk event ini.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Registration Summary -->
                <div class="registration-summary" id="registrationSummary" style="display: none;">
                    <h4 style="margin-bottom: 15px; color: #333;">
                        <i class="fas fa-receipt"></i>
                        Ringkasan Pendaftaran
                    </h4>

                    <div id="selectedSessionsList" class="selected-sessions-list">
                        <!-- Selected sessions will be populated here -->
                    </div>
                    <div class="summary-content">
                        <div class="summary-row">
                            <span>Sesi yang dipilih:</span>
                            <span id="selectedSessionsCount">0 sesi</span>
                        </div>
                        <div class="summary-row total-row">
                            <span><strong>Total Pembayaran:</strong></span>
                            <span id="totalPayment"><strong>Rp 0</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn-draft" onclick="saveDraft()">
                        <i class="fas fa-save"></i>
                        Simpan Draft
                    </button>
                    <button type="submit" class="btn-continue" id="continueBtn" disabled>
                        <i class="fas fa-arrow-right"></i>
                        Lanjut ke Pembayaran
                    </button>
                </div>
            </div>

            <!-- Hidden inputs -->
            <input type="hidden" name="payment_amount" id="paymentAmountInput" value="0">
            <input type="hidden" name="is_draft" value="0" id="isDraftInput">
        </form>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Registration form loaded');

            // Load existing draft from server-side data
            loadExistingDraft();

            updateSelection();

            // Add event listeners to all session checkboxes
            const sessionCheckboxes = document.querySelectorAll('.session-checkbox');
            sessionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelection();
                });
            });
        });

        // Load existing draft from server-side data
        function loadExistingDraft() {
            const draftData = @json($draft ?? null);

            if (draftData && draftData.selected_sessions) {
                console.log('Loading draft with sessions:', draftData.selected_sessions);

                draftData.selected_sessions.forEach(sessionId => {
                    const checkbox = document.querySelector(`input[value="${sessionId}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                        checkbox.closest('.session-card').classList.add('selected');
                    }
                });

                updateSelection();
            }
        }

        function toggleSession(element) {
            if (element.classList.contains('full')) {
                console.log('Session is full, cannot select');
                return;
            }

            const checkbox = element.querySelector('.session-checkbox');
            if (!checkbox) {
                console.log('No checkbox found in session card');
                return;
            }

            checkbox.checked = !checkbox.checked;
            console.log('Checkbox checked:', checkbox.checked);

            if (checkbox.checked) {
                element.classList.add('selected');
            } else {
                element.classList.remove('selected');
            }

            updateSelection();
        }

        function updateSelection() {
            const selectedCheckboxes = document.querySelectorAll('.session-checkbox:checked');
            const selectedCount = selectedCheckboxes.length;

            console.log('Selected sessions count:', selectedCount);

            // Calculate total payment
            let totalPayment = 0;
            const selectedSessionsData = [];

            selectedCheckboxes.forEach(checkbox => {
                const sessionCard = checkbox.closest('.session-card');
                const sessionFee = parseInt(sessionCard.dataset.sessionFee) || 0;
                const sessionTitle = sessionCard.querySelector('.session-info h4').textContent.trim();

                totalPayment += sessionFee;
                selectedSessionsData.push({
                    title: sessionTitle,
                    fee: sessionFee
                });
            });

            // Update summary
            const summaryElement = document.getElementById('registrationSummary');
            const continueBtn = document.getElementById('continueBtn');
            const selectedCountElement = document.getElementById('selectedSessionsCount');
            const totalPaymentElement = document.getElementById('totalPayment');
            const paymentAmountInput = document.getElementById('paymentAmountInput');
            const selectedSessionsList = document.getElementById('selectedSessionsList');

            if (selectedCount > 0) {
                if (summaryElement) summaryElement.style.display = 'block';
                if (continueBtn) {
                    continueBtn.disabled = false;
                    continueBtn.classList.remove('disabled');
                }
                if (selectedCountElement) selectedCountElement.textContent = selectedCount + ' sesi';
                if (totalPaymentElement) {
                    totalPaymentElement.innerHTML = totalPayment > 0 ?
                        '<strong>Rp ' + totalPayment.toLocaleString('id-ID') + '</strong>' :
                        '<strong>GRATIS</strong>';
                }
                if (paymentAmountInput) paymentAmountInput.value = totalPayment;

                // Update selected sessions list
                if (selectedSessionsList) {
                    selectedSessionsList.innerHTML = selectedSessionsData.map(session =>
                        `
                 
                
                <div class="selected-session-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Session ${session.title}</h5>
                        <span class="session-price text-success fw-bold">
                ${session.fee > 0 ? 'Rp ' + session.fee.toLocaleString('id-ID') : 'Gratis'}        </span>
                    </div>

                    @if (isset($session['title']) && $session['title'])
                        <p class="session-title">{{ $session['title'] }}</p>
                    @endif

                    <div class="session-details-small text-muted">
                        <span><i class="bx bx-calendar"></i> {{ \Carbon\Carbon::parse($session['date'])->format('d M Y') }}</span>
                        <span><i class="bx bx-clock"></i> {{ $session['start_time'] }} - {{ $session['end_time'] }}</span>
                        <span><i class="bx bx-map-marker-alt"></i> {{ $session['location'] }}</span>
                    </div>
                </div>

                `
                    ).join('');
                }
            } else {
                if (summaryElement) summaryElement.style.display = 'none';
                if (continueBtn) {
                    continueBtn.disabled = true;
                    continueBtn.classList.add('disabled');
                }
                if (paymentAmountInput) paymentAmountInput.value = 0;
            }
        }

        function saveDraft() {
            console.log('Save draft clicked');

            const selectedSessions = Array.from(document.querySelectorAll('.session-checkbox:checked'))
                .map(checkbox => checkbox.value);

            console.log('Selected sessions for draft:', selectedSessions);

            if (selectedSessions.length === 0) {
                alert('Pilih minimal satu sesi untuk menyimpan draft');
                return;
            }

            // Set form to draft mode
            const isDraftInput = document.getElementById('isDraftInput');
            if (isDraftInput) {
                isDraftInput.value = '1';
            }

            // Update button state
            const draftBtn = document.querySelector('.btn-draft');
            if (draftBtn) {
                draftBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan Draft...';
                draftBtn.disabled = true;
            }

            // Submit form
            const form = document.getElementById('registrationForm');
            if (form) {
                console.log('Submitting form for draft');
                form.submit();
            } else {
                console.error('Form not found');
            }
        }

        // Add loading state when form is submitted
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            console.log('Form submitted');

            const selectedSessions = Array.from(document.querySelectorAll('.session-checkbox:checked'));

            if (selectedSessions.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal satu sesi');
                return false;
            }

            const submitBtn = document.querySelector('button[type="submit"]');
            const isDraft = document.getElementById('isDraftInput').value === '1';

            if (submitBtn) {
                if (isDraft) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan Draft...';
                } else {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                }
                submitBtn.disabled = true;
            }
        });

        function showPosterModal(src) {
            const modal = document.getElementById('posterModal');
            const img = document.getElementById('posterModalImg');
            if (modal && img) {
                img.src = src;
                modal.style.display = 'flex';
            }
        }

        function closePosterModal() {
            const modal = document.getElementById('posterModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Close modal on background click
        const posterModal = document.getElementById('posterModal');
        if (posterModal) {
            posterModal.addEventListener('click', function(e) {
                if (e.target === this) closePosterModal();
            });
        }
    </script>
@endsection
