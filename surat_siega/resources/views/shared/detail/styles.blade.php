<style>
    .detail-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #eef4fb 100%);
        border: 1px solid #dde7f2;
        border-radius: 1rem;
        padding: 1.35rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .detail-hero__icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 10px 22px rgba(59, 130, 246, 0.16);
    }

    .detail-hero__title {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
        color: #1e3a5f;
    }

    .detail-hero__desc {
        margin: .25rem 0 0;
        color: #64748b;
    }

    .detail-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        margin-bottom: 1rem;
    }

    .detail-chip {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        padding: .45rem .75rem;
        border-radius: .65rem;
        font-weight: 600;
        font-size: .86rem;
    }

    .detail-preview-shell {
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
    }

    .detail-preview-paper {
        background: #fff;
        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
    }

    .detail-card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .detail-card .card-header {
        background: #fff;
        border-bottom: 1px solid #edf2f7;
        font-weight: 700;
        padding: 1rem 1.25rem;
    }

    .detail-card .card-body {
        padding: 1.1rem 1.25rem;
    }

    .detail-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .detail-list li {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: .65rem 0;
        border-bottom: 1px dashed #e2e8f0;
    }

    .detail-list li:last-child {
        border-bottom: none;
    }

    .detail-list .label {
        color: #64748b;
        font-weight: 600;
    }

    .detail-list .value {
        color: #1e293b;
        font-weight: 700;
        text-align: right;
        word-break: break-word;
    }

    .attachment-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: .85rem;
        padding: .9rem 1rem;
        margin-bottom: .75rem;
    }

    .sticky-sidebar {
        position: sticky;
        top: 20px;
    }
</style>
