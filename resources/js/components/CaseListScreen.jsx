import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';

export default function CaseListScreen({ navigate, initialStatus = '' }) {
    const { user, apiFetch } = useAuth();
    const [cases, setCases] = useState([]);
    const [pagination, setPagination] = useState({});
    const [currentPage, setCurrentPage] = useState(1);
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState(initialStatus);
    const [priorityFilter, setPriorityFilter] = useState('');
    const [sortBy, setSortBy] = useState('id');
    const [loading, setLoading] = useState(false);
    const [actionMessage, setActionMessage] = useState(null);

    const statuses = ['Draft', 'Planning', 'In Review', 'Approved', 'Completed', 'Cancelled'];
    const priorities = ['Low', 'Medium', 'High'];

    useEffect(() => {
        fetchCases();
    }, [currentPage, statusFilter, priorityFilter, sortBy]);

    const fetchCases = async () => {
        setLoading(true);
        let query = `/api/cases?page=${currentPage}`;
        if (search) query += `&search=${encodeURIComponent(search)}`;
        if (statusFilter) query += `&status=${encodeURIComponent(statusFilter)}`;
        if (priorityFilter) query += `&priority=${encodeURIComponent(priorityFilter)}`;

        const res = await apiFetch(query);
        if (res.ok && res.data.success) {
            setCases(res.data.data.data || []);
            setPagination({
                current_page: res.data.data.current_page,
                last_page: res.data.data.last_page,
                total: res.data.data.total,
                from: res.data.data.from,
                to: res.data.data.to,
            });
        }
        setLoading(false);
    };

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        setCurrentPage(1);
        fetchCases();
    };

    const handleDelete = async (caseItem) => {
        if (!window.confirm(`Are you sure you want to permanently delete case ${caseItem.case_number}?`)) {
            return;
        }

        const res = await apiFetch(`/api/cases/${caseItem.id}`, {
            method: 'DELETE',
        });

        if (res.ok && res.data.success) {
            setActionMessage({ type: 'success', text: `Medical case ${caseItem.case_number} has been deleted.` });
            fetchCases();
        } else {
            setActionMessage({
                type: 'error',
                text: res.data.message || 'Unauthorized: Only Admin users can delete cases.',
            });
        }
    };

    const canCreate = user && (user.role === 'admin' || user.role === 'engineer');
    const canEdit = user && (user.role === 'admin' || user.role === 'engineer');
    const canDelete = user && user.role === 'admin';

    const getStatusSlug = (status) => {
        return (status || '').toLowerCase().replace(/\s+/g, '-');
    };

    return (
        <div>
            <div style={{ marginBottom: '28px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', flexWrap: 'wrap', gap: '16px' }}>
                <div>
                    <h1 style={{ marginBottom: '6px' }}>Medical Implant Cases</h1>
                    <p style={{ color: 'var(--color-muted)' }}>
                        Browse, search, and manage patient-specific implant cases across clinical statuses.
                    </p>
                </div>

                {canCreate && (
                    <button
                        type="button"
                        className="btn btn-primary"
                        onClick={() => navigate('cases-create')}
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Create New Case</span>
                    </button>
                )}
            </div>

            {actionMessage && (
                <div className={`alert alert-${actionMessage.type === 'success' ? 'success' : 'danger'}`}>
                    <span>{actionMessage.text}</span>
                </div>
            )}

            {/* Search, Filter & Sort Controls */}
            <div className="card" style={{ padding: '20px 24px', marginBottom: '24px' }}>
                <form
                    onSubmit={handleSearchSubmit}
                    style={{ display: 'flex', gap: '16px', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between' }}
                >
                    <div className="search-pill-container" style={{ flex: 1, minWidth: '260px' }}>
                        <svg className="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input
                            type="text"
                            className="search-input-pill"
                            placeholder="Search by Case Number or Surgeon..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </div>

                    <div style={{ display: 'flex', gap: '12px', flexWrap: 'wrap', alignItems: 'center' }}>
                        <select
                            className="form-control"
                            value={statusFilter}
                            onChange={(e) => {
                                setStatusFilter(e.target.value);
                                setCurrentPage(1);
                            }}
                            style={{ width: 'auto', height: '44px', padding: '8px 16px', borderRadius: 'var(--radius-pill)', fontSize: '13px' }}
                        >
                            <option value="">All Statuses</option>
                            {statuses.map((st) => (
                                <option key={st} value={st}>{st}</option>
                            ))}
                        </select>

                        <select
                            className="form-control"
                            value={priorityFilter}
                            onChange={(e) => {
                                setPriorityFilter(e.target.value);
                                setCurrentPage(1);
                            }}
                            style={{ width: 'auto', height: '44px', padding: '8px 16px', borderRadius: 'var(--radius-pill)', fontSize: '13px' }}
                        >
                            <option value="">All Priorities</option>
                            {priorities.map((pr) => (
                                <option key={pr} value={pr}>{pr} Priority</option>
                            ))}
                        </select>

                        <select
                            className="form-control"
                            value={sortBy}
                            onChange={(e) => setSortBy(e.target.value)}
                            style={{ width: 'auto', height: '44px', padding: '8px 16px', borderRadius: 'var(--radius-pill)', fontSize: '13px' }}
                        >
                            <option value="id">Sort by: Newest First</option>
                            <option value="case_number">Sort by: Case Number</option>
                            <option value="surgery_date">Sort by: Surgery Date</option>
                            <option value="status">Sort by: Status</option>
                            <option value="priority">Sort by: Priority</option>
                        </select>

                        <button type="submit" className="btn btn-secondary btn-sm" style={{ height: '44px', padding: '0 20px' }}>
                            Search
                        </button>

                        {(search || statusFilter || priorityFilter) && (
                            <button
                                type="button"
                                className="btn btn-secondary btn-sm"
                                onClick={() => {
                                    setSearch('');
                                    setStatusFilter('');
                                    setPriorityFilter('');
                                    setCurrentPage(1);
                                }}
                                style={{ height: '44px', padding: '0 16px' }}
                            >
                                Reset
                            </button>
                        )}
                    </div>
                </form>
            </div>

            {/* Cases Table */}
            <div className="card" style={{ padding: 0, overflow: 'hidden' }}>
                <div className="table-responsive">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Case Number</th>
                                <th>Patient Reference</th>
                                <th>Surgeon</th>
                                <th>Implant Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Surgery Date</th>
                                <th>Created By</th>
                                <th style={{ textAlign: 'right' }}>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {cases.map((c) => (
                                <tr key={c.id}>
                                    <td>
                                        <button
                                            type="button"
                                            className="mono"
                                            onClick={() => navigate('cases-show', { id: c.id })}
                                            style={{
                                                background: 'none',
                                                border: 'none',
                                                fontWeight: 600,
                                                color: 'var(--color-primary)',
                                                cursor: 'pointer',
                                                padding: 0,
                                            }}
                                        >
                                            {c.case_number}
                                        </button>
                                    </td>
                                    <td className="mono" style={{ fontSize: '13px', color: 'var(--color-body)' }}>
                                        {c.patient_reference}
                                    </td>
                                    <td style={{ fontWeight: 500 }}>{c.surgeon_name}</td>
                                    <td style={{ color: 'var(--color-body)', maxWidth: '200px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                                        {c.implant_type}
                                    </td>
                                    <td>
                                        <span className={`badge badge-status-${getStatusSlug(c.status)}`}>
                                            {c.status}
                                        </span>
                                    </td>
                                    <td>
                                        <span className={`priority-${(c.priority || '').toLowerCase()}`}>
                                            &bull; {c.priority}
                                        </span>
                                    </td>
                                    <td className="mono" style={{ fontSize: '13px' }}>
                                        {c.surgery_date ? c.surgery_date.substring(0, 10) : '—'}
                                    </td>
                                    <td style={{ fontSize: '13px', color: 'var(--color-muted)' }}>
                                        {c.creator?.name || 'System'}
                                    </td>
                                    <td style={{ textAlign: 'right', whiteSpace: 'nowrap' }}>
                                        <div style={{ display: 'inline-flex', gap: '6px', alignItems: 'center' }}>
                                            <button
                                                type="button"
                                                className="btn btn-secondary btn-sm"
                                                onClick={() => navigate('cases-show', { id: c.id })}
                                                style={{ height: '32px', padding: '0 12px', fontSize: '12px' }}
                                            >
                                                View
                                            </button>

                                            {canEdit && (
                                                <button
                                                    type="button"
                                                    className="btn btn-secondary btn-sm"
                                                    onClick={() => navigate('cases-edit', { id: c.id })}
                                                    style={{ height: '32px', padding: '0 12px', fontSize: '12px' }}
                                                >
                                                    Edit
                                                </button>
                                            )}

                                            {canDelete && (
                                                <button
                                                    type="button"
                                                    className="btn btn-danger btn-sm"
                                                    onClick={() => handleDelete(c)}
                                                    style={{ height: '32px', padding: '0 10px', fontSize: '12px' }}
                                                >
                                                    Delete
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {cases.length === 0 && !loading && (
                                <tr>
                                    <td colSpan="9" style={{ textAlign: 'center', padding: '48px', color: 'var(--color-muted)' }}>
                                        <div style={{ fontSize: '16px', fontWeight: 500, marginBottom: '6px' }}>No cases found</div>
                                        <p style={{ fontSize: '13px' }}>Try adjusting your search keywords or filter criteria.</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination Controls (10 per page) */}
                {pagination.last_page > 1 && (
                    <div style={{ padding: '20px 24px', borderTop: '1px solid var(--color-hairline)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px' }}>
                        <div style={{ fontSize: '13px', color: 'var(--color-muted)' }}>
                            Showing <strong>{pagination.from}</strong> to <strong>{pagination.to}</strong> of <strong>{pagination.total}</strong> total cases
                        </div>
                        <div style={{ display: 'flex', gap: '6px' }}>
                            <button
                                type="button"
                                className="btn btn-secondary btn-sm"
                                disabled={currentPage <= 1}
                                onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                            >
                                &larr; Previous
                            </button>
                            <span style={{ display: 'inline-flex', alignItems: 'center', padding: '0 12px', fontSize: '13px', fontWeight: 600 }}>
                                Page {currentPage} of {pagination.last_page}
                            </span>
                            <button
                                type="button"
                                className="btn btn-secondary btn-sm"
                                disabled={currentPage >= pagination.last_page}
                                onClick={() => setCurrentPage(prev => Math.min(prev + 1, pagination.last_page))}
                            >
                                Next &rarr;
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
