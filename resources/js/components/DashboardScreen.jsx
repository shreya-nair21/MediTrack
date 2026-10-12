import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';

export default function DashboardScreen({ navigate }) {
    const { user, apiFetch } = useAuth();
    const [metrics, setMetrics] = useState({
        total: 0,
        planning: 0,
        inReview: 0,
        approved: 0,
        completed: 0,
    });
    const [recentCases, setRecentCases] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async () => {
        setLoading(true);
        const res = await apiFetch('/api/cases?page=1');
        if (res.ok && res.data.success) {
            const pagination = res.data.data;
            const casesList = pagination.data || [];
            
            // To get comprehensive counts across all cases, calculate or fetch
            let total = pagination.total || casesList.length;
            let planning = 0;
            let inReview = 0;
            let approved = 0;
            let completed = 0;

            casesList.forEach(c => {
                if (c.status === 'Planning') planning++;
                else if (c.status === 'In Review') inReview++;
                else if (c.status === 'Approved') approved++;
                else if (c.status === 'Completed') completed++;
            });

            setMetrics({
                total,
                planning,
                inReview,
                approved,
                completed,
            });

            setRecentCases(casesList.slice(0, 5));
        }
        setLoading(false);
    };

    const canCreate = user && (user.role === 'admin' || user.role === 'engineer');

    const getStatusSlug = (status) => {
        return (status || '').toLowerCase().replace(/\s+/g, '-');
    };

    return (
        <div>
            <div style={{ marginBottom: '32px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', flexWrap: 'wrap', gap: '16px' }}>
                <div>
                    <h1 style={{ marginBottom: '6px' }}>Clinical Case Dashboard</h1>
                    <p style={{ color: 'var(--color-muted)' }}>
                        Real-time monitoring of medical implant cases from draft to surgical completion.
                    </p>
                </div>

                <div style={{ display: 'flex', gap: '12px' }}>
                    <button
                        type="button"
                        className="btn btn-secondary"
                        onClick={() => navigate('cases')}
                    >
                        View All Cases
                    </button>
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
                            <span>New Case</span>
                        </button>
                    )}
                </div>
            </div>

            {/* Metric KPI Cards */}
            <div className="metric-grid">
                <div
                    className="metric-card"
                    style={{ cursor: 'pointer' }}
                    onClick={() => navigate('cases')}
                >
                    <div className="metric-label">Total Cases</div>
                    <div className="metric-value">{metrics.total}</div>
                    <div style={{ marginTop: '10px', fontSize: '12px', color: 'var(--color-muted)' }}>
                        All logged implant cases
                    </div>
                </div>

                <div
                    className="metric-card"
                    style={{ cursor: 'pointer' }}
                    onClick={() => navigate('cases', { status: 'Planning' })}
                >
                    <div className="metric-label">Planning</div>
                    <div className="metric-value" style={{ color: '#b07e00' }}>{metrics.planning}</div>
                    <div style={{ marginTop: '10px', fontSize: '12px', color: 'var(--color-muted)' }}>
                        Surgical planning & CAD
                    </div>
                </div>

                <div
                    className="metric-card"
                    style={{ cursor: 'pointer' }}
                    onClick={() => navigate('cases', { status: 'In Review' })}
                >
                    <div className="metric-label">In Review</div>
                    <div className="metric-value" style={{ color: '#1971c2' }}>{metrics.inReview}</div>
                    <div style={{ marginTop: '10px', fontSize: '12px', color: 'var(--color-muted)' }}>
                        Clinical peer review
                    </div>
                </div>

                <div
                    className="metric-card"
                    style={{ cursor: 'pointer' }}
                    onClick={() => navigate('cases', { status: 'Approved' })}
                >
                    <div className="metric-label">Approved</div>
                    <div className="metric-value" style={{ color: '#2b8a3e' }}>{metrics.approved}</div>
                    <div style={{ marginTop: '10px', fontSize: '12px', color: 'var(--color-muted)' }}>
                        Cleared for surgery
                    </div>
                </div>

                <div
                    className="metric-card"
                    style={{ cursor: 'pointer' }}
                    onClick={() => navigate('cases', { status: 'Completed' })}
                >
                    <div className="metric-label">Completed</div>
                    <div className="metric-value" style={{ color: '#087f5b' }}>{metrics.completed}</div>
                    <div style={{ marginTop: '10px', fontSize: '12px', color: 'var(--color-muted)' }}>
                        Surgery finalized
                    </div>
                </div>
            </div>

            {/* Recent Cases Card */}
            <div className="card" style={{ marginTop: '12px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '24px' }}>
                    <div>
                        <h2>Recently Updated Cases</h2>
                        <p style={{ color: 'var(--color-muted)', fontSize: '13px', marginTop: '2px' }}>
                            Latest patient-specific implant cases entering the system.
                        </p>
                    </div>
                    <button
                        type="button"
                        className="btn btn-secondary btn-sm"
                        onClick={() => navigate('cases')}
                    >
                        Explore All &rarr;
                    </button>
                </div>

                <div className="table-responsive">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Case Number</th>
                                <th>Patient Ref</th>
                                <th>Surgeon</th>
                                <th>Implant Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Surgery Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {recentCases.map((c) => (
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
                                    <td style={{ color: 'var(--color-body)' }}>{c.implant_type}</td>
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
                                    <td>
                                        <button
                                            type="button"
                                            className="btn btn-secondary btn-sm"
                                            onClick={() => navigate('cases-show', { id: c.id })}
                                            style={{ height: '32px', padding: '0 12px', fontSize: '12px' }}
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            ))}
                            {recentCases.length === 0 && !loading && (
                                <tr>
                                    <td colSpan="8" style={{ textAlign: 'center', padding: '40px', color: 'var(--color-muted)' }}>
                                        No medical cases found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}
