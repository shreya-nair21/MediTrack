import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';

export default function CaseDetailsScreen({ caseId, navigate }) {
    const { user, apiFetch } = useAuth();
    const [caseData, setCaseData] = useState(null);
    const [holidayNotice, setHolidayNotice] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchCase();
    }, [caseId]);

    const fetchCase = async () => {
        setLoading(true);
        const res = await apiFetch(`/api/cases/${caseId}`);
        if (res.ok && res.data.success) {
            const data = res.data.data;
            setCaseData(data);

            if (data.surgery_date) {
                checkHoliday(data.surgery_date);
            }
        } else {
            setError(res.data.message || 'Medical case not found.');
        }
        setLoading(false);
    };

    const checkHoliday = async (surgeryDate) => {
        try {
            const dateStr = surgeryDate.substring(0, 10);
            const year = dateStr.substring(0, 4);
            const res = await fetch(`https://date.nager.at/api/v3/PublicHolidays/${year}/US`, { signal: AbortSignal.timeout(3000) });
            if (res.ok) {
                const holidays = await res.json();
                const match = holidays.find(h => h.date === dateStr);
                if (match) {
                    setHolidayNotice(`Public Holiday Alert: ${match.name} (${match.localName}) falls on this surgery date.`);
                }
            }
        } catch (e) {
            // Graceful fallback
        }
    };

    const handleDelete = async () => {
        if (!caseData) return;
        if (!window.confirm(`Are you sure you want to permanently delete case ${caseData.case_number}?`)) {
            return;
        }

        const res = await apiFetch(`/api/cases/${caseData.id}`, {
            method: 'DELETE',
        });

        if (res.ok && res.data.success) {
            navigate('cases');
        } else {
            alert(res.data.message || 'Unauthorized: Only Admin users can delete cases.');
        }
    };

    const canEdit = user && (user.role === 'admin' || user.role === 'engineer');
    const canDelete = user && user.role === 'admin';

    const getStatusSlug = (status) => {
        return (status || '').toLowerCase().replace(/\s+/g, '-');
    };

    if (loading) {
        return (
            <div style={{ padding: '60px', textAlign: 'center', color: 'var(--color-muted)' }}>
                Loading case details...
            </div>
        );
    }

    if (error || !caseData) {
        return (
            <div className="card" style={{ textAlign: 'center', padding: '40px' }}>
                <h2 style={{ color: 'var(--color-semantic-down)', marginBottom: '12px' }}>Case Not Found</h2>
                <p style={{ color: 'var(--color-muted)', marginBottom: '20px' }}>{error || 'Unable to locate case records.'}</p>
                <button type="button" className="btn btn-secondary" onClick={() => navigate('cases')}>
                    &larr; Return to Cases
                </button>
            </div>
        );
    }

    return (
        <div style={{ maxWidth: '860px', margin: '0 auto' }}>
            {/* Top Navigation & Actions */}
            <div style={{ marginBottom: '24px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', flexWrap: 'wrap', gap: '16px' }}>
                <div>
                    <button
                        type="button"
                        onClick={() => navigate('cases')}
                        style={{ background: 'none', border: 'none', color: 'var(--color-muted)', cursor: 'pointer', fontSize: '13px', fontWeight: 500, padding: 0 }}
                    >
                        &larr; Back to Cases Directory
                    </button>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginTop: '8px' }}>
                        <h1 className="mono" style={{ margin: 0, fontSize: '30px' }}>
                            {caseData.case_number}
                        </h1>
                        <span className={`badge badge-status-${getStatusSlug(caseData.status)}`} style={{ fontSize: '14px', padding: '6px 14px' }}>
                            {caseData.status}
                        </span>
                    </div>
                </div>

                <div style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
                    {canEdit && (
                        <button
                            type="button"
                            className="btn btn-secondary"
                            onClick={() => navigate('cases-edit', { id: caseData.id })}
                        >
                            Edit Case
                        </button>
                    )}

                    {canDelete && (
                        <button
                            type="button"
                            className="btn btn-danger"
                            onClick={handleDelete}
                        >
                            Delete Case
                        </button>
                    )}
                </div>
            </div>

            {/* Public Holiday Notification (Bonus) */}
            {holidayNotice && (
                <div className="alert alert-success" style={{ backgroundColor: '#fff9db', color: '#8f6b00', border: '1px solid #ffe066', marginBottom: '24px' }}>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <strong>Notice:</strong> {holidayNotice}
                        <div style={{ fontSize: '12px', opacity: 0.85 }}>Hospital surgical theater schedules may be affected by holiday staffing.</div>
                    </div>
                </div>
            )}

            {/* Main Clinical Case Specification Card */}
            <div className="card" style={{ marginBottom: '24px' }}>
                <h2 style={{ fontSize: '18px', marginBottom: '20px', borderBottom: '1px solid var(--color-hairline)', paddingBottom: '12px' }}>
                    Clinical Case Specification
                </h2>

                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(240px, 1fr))', gap: '24px' }}>
                    <div>
                        <div style={{ fontSize: '12px', fontWeight: 600, textTransform: 'uppercase', color: 'var(--color-muted)', marginBottom: '4px' }}>
                            Patient Reference Code
                        </div>
                        <div className="mono" style={{ fontSize: '16px', fontWeight: 600, color: 'var(--color-ink)' }}>
                            {caseData.patient_reference}
                        </div>
                        <div style={{ fontSize: '11px', color: 'var(--color-muted)' }}>Pseudo-anonymized identifier</div>
                    </div>

                    <div>
                        <div style={{ fontSize: '12px', fontWeight: 600, textTransform: 'uppercase', color: 'var(--color-muted)', marginBottom: '4px' }}>
                            Surgeon Name
                        </div>
                        <div style={{ fontSize: '16px', fontWeight: 600, color: 'var(--color-ink)' }}>
                            {caseData.surgeon_name}
                        </div>
                        <div style={{ fontSize: '11px', color: 'var(--color-muted)' }}>Primary Lead Clinician</div>
                    </div>

                    <div>
                        <div style={{ fontSize: '12px', fontWeight: 600, textTransform: 'uppercase', color: 'var(--color-muted)', marginBottom: '4px' }}>
                            Implant Type / Model
                        </div>
                        <div style={{ fontSize: '16px', fontWeight: 600, color: 'var(--color-ink)' }}>
                            {caseData.implant_type}
                        </div>
                        <div style={{ fontSize: '11px', color: 'var(--color-muted)' }}>Patient-specific device hardware</div>
                    </div>

                    <div>
                        <div style={{ fontSize: '12px', fontWeight: 600, textTransform: 'uppercase', color: 'var(--color-muted)', marginBottom: '4px' }}>
                            Clinical Priority
                        </div>
                        <div>
                            <span className={`priority-${(caseData.priority || '').toLowerCase()}`} style={{ fontSize: '16px' }}>
                                &bull; {caseData.priority} Priority
                            </span>
                        </div>
                    </div>

                    <div>
                        <div style={{ fontSize: '12px', fontWeight: 600, textTransform: 'uppercase', color: 'var(--color-muted)', marginBottom: '4px' }}>
                            Scheduled Surgery Date
                        </div>
                        <div className="mono" style={{ fontSize: '16px', fontWeight: 600, color: 'var(--color-ink)' }}>
                            {caseData.surgery_date ? caseData.surgery_date.substring(0, 10) : 'Not scheduled yet'}
                        </div>
                    </div>

                    <div>
                        <div style={{ fontSize: '12px', fontWeight: 600, textTransform: 'uppercase', color: 'var(--color-muted)', marginBottom: '4px' }}>
                            Lifecycle State
                        </div>
                        <div>
                            <span className={`badge badge-status-${getStatusSlug(caseData.status)}`}>
                                {caseData.status}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Audit Trail Box */}
            <div className="card" style={{ backgroundColor: 'var(--color-surface-soft)' }}>
                <h2 style={{ fontSize: '15px', marginBottom: '16px', color: 'var(--color-ink)' }}>
                    Audit Trail &amp; Regulatory Traceability
                </h2>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '16px', fontSize: '13px' }}>
                    <div>
                        <span style={{ color: 'var(--color-muted)' }}>Created By:</span><br />
                        <strong>{caseData.creator?.name || 'System'}</strong> ({caseData.creator?.email || 'N/A'})
                        <br />
                        <span className={`badge badge-role-${caseData.creator?.role || 'viewer'}`} style={{ marginTop: '4px' }}>
                            {caseData.creator?.role ? caseData.creator.role.charAt(0).toUpperCase() + caseData.creator.role.slice(1) : 'User'}
                        </span>
                    </div>
                    <div>
                        <span style={{ color: 'var(--color-muted)' }}>Created At:</span><br />
                        <span className="mono">{caseData.created_at ? new Date(caseData.created_at).toLocaleString() : 'N/A'}</span>
                    </div>
                    <div>
                        <span style={{ color: 'var(--color-muted)' }}>Last Updated At:</span><br />
                        <span className="mono">{caseData.updated_at ? new Date(caseData.updated_at).toLocaleString() : 'N/A'}</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
