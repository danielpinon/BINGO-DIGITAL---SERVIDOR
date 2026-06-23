@extends('layouts.app', ['activePage' => 'bingos', 'titlePage' => __('Gerando PDF')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Gerando PDF das Cartelas</h4>
                        <p class="card-category">{{ $bingo->name }}</p>
                    </div>
                    <div class="card-body">
                        <div class="pdf-progress-shell">
                            <div class="pdf-progress-icon">
                                <i class="material-icons">picture_as_pdf</i>
                            </div>

                            <h3 class="pdf-progress-title" data-pdf-title>Preparando geração</h3>
                            <p class="pdf-progress-message" data-pdf-message>Solicitando geração do PDF. O cron irá processar em segundo plano.</p>

                            <div class="pdf-progress-track">
                                <div class="pdf-progress-bar" data-pdf-bar style="width: 5%;"></div>
                            </div>

                            <div class="pdf-progress-meta">
                                <span data-pdf-percent>5%</span>
                                <span data-pdf-status>Aguardando</span>
                            </div>

                            <div class="pdf-progress-actions">
                                <a href="{{ route('bingos.index') }}" class="btn btn-outline-primary">
                                    Voltar
                                </a>
                                <a href="#" class="btn btn-success d-none" data-pdf-download>
                                    <i class="material-icons">download</i> Baixar PDF
                                </a>
                                <form action="{{ route('cards.pdf.start', $bingo) }}" method="POST" class="d-none" data-pdf-retry>
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="material-icons">refresh</i> Tentar novamente
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .pdf-progress-shell {
        max-width: 720px;
        margin: 20px auto;
        text-align: center;
        padding: 18px 0;
    }

    .pdf-progress-icon {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        margin: 0 auto 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3e8ff;
        color: #6d28d9;
    }

    .pdf-progress-icon .material-icons {
        font-size: 42px;
    }

    .pdf-progress-title {
        margin: 0 0 8px;
        color: #1f2937;
        font-weight: 700;
    }

    .pdf-progress-message {
        min-height: 28px;
        color: #6b7280;
        margin-bottom: 24px;
        font-size: 1rem;
    }

    .pdf-progress-track {
        width: 100%;
        height: 18px;
        background: #eef2f7;
        border-radius: 999px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.12);
    }

    .pdf-progress-bar {
        height: 100%;
        min-width: 5%;
        border-radius: 999px;
        background: linear-gradient(90deg, #4c1d95, #8b5cf6);
        transition: width 0.45s ease;
    }

    .pdf-progress-meta {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-top: 12px;
        color: #64748b;
        font-weight: 600;
    }

    .pdf-progress-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 28px;
    }
</style>
@endpush

@push('js')
<script>
    (function () {
        const progressUrl = @json(route('cards.pdf.progress', $bingo));
        const bar = document.querySelector('[data-pdf-bar]');
        const percent = document.querySelector('[data-pdf-percent]');
        const status = document.querySelector('[data-pdf-status]');
        const title = document.querySelector('[data-pdf-title]');
        const message = document.querySelector('[data-pdf-message]');
        const download = document.querySelector('[data-pdf-download]');
        const retry = document.querySelector('[data-pdf-retry]');
        let intervalId = null;

        const setProgress = (data) => {
            const value = Math.max(5, Math.min(100, Number(data.percent || 5)));
            bar.style.width = value + '%';
            percent.textContent = value + '%';
            status.textContent = data.status_label || data.status || 'Aguardando';
            message.textContent = data.message || 'Consultando servidor.';

            if (data.ready) {
                title.textContent = 'PDF pronto';
                download.href = data.download_url;
                download.classList.remove('d-none');
                clearInterval(intervalId);
                return;
            }

            if (data.failed) {
                title.textContent = 'Não foi possível gerar';
                retry.classList.remove('d-none');
                clearInterval(intervalId);
                return;
            }

            title.textContent = data.status === 'processing' ? 'Gerando PDF' : 'Aguardando processamento';
        };

        const poll = () => {
            fetch(progressUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => response.json())
                .then(setProgress)
                .catch(() => {
                    message.textContent = 'Aguardando resposta do servidor...';
                });
        };

        poll();
        intervalId = setInterval(poll, 2500);
    })();
</script>
@endpush
