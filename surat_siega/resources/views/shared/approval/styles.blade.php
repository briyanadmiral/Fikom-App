<style>
    .approval-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #eef4fb 100%);
        border: 1px solid #dde7f2;
        border-radius: 1rem;
        padding: 1.35rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .approval-hero__icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        box-shadow: 0 8px 18px rgba(245, 158, 11, 0.25);
        flex-shrink: 0;
    }

    .approval-hero__title {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
        color: #7c4a03;
    }

    .approval-hero__desc {
        margin: .25rem 0 0;
        color: #64748b;
    }

    .approval-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .approval-card .card-header {
        background: #fff;
        border-bottom: 1px solid #edf2f7;
        font-weight: 700;
        padding: 1rem 1.25rem;
    }

    .approval-card .card-body {
        padding: 1.1rem 1.25rem;
    }

    .approval-info-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .approval-info-list li {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: .6rem 0;
        border-bottom: 1px dashed #e5edf5;
    }

    .approval-info-list li:last-child {
        border-bottom: none;
    }

    .approval-info-list .label {
        color: #64748b;
        font-weight: 600;
    }

    .approval-info-list .value {
        text-align: right;
        font-weight: 700;
        color: #1e293b;
    }

    .approval-control .form-label {
        font-weight: 600;
    }

    .approval-tip {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: .85rem;
        padding: .85rem 1rem;
        color: #475569;
        font-size: .9rem;
    }

    .approval-preview-shell {
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
        border-radius: 1rem;
        padding: 1.25rem;
        min-height: 400px;
        position: relative;
    }

    .approval-preview-pane {
        background: #fff;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
        position: relative;
    }

    .approval-preview-spinner {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 20;
        display: none;
    }
</style>
