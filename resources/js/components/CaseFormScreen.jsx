import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';

export default function CaseFormScreen({ caseId, navigate }) {
    const { apiFetch } = useAuth();
    const isEdit = Boolean(caseId);

    const [formData, setFormData] = useState({
        case_number: 'CASE-' + Math.floor(1000 + Math.random() * 9000),
        patient_reference: '',
        surgeon_name: '',
        implant_type: '',
        status: 'Draft',
        priority: 'Medium',
        surgery_date: '',
    });

    const [fieldErrors, setFieldErrors] = useState({});
    const [globalError, setGlobalError] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [loadingCase, setLoadingCase] = useState(isEdit);

    const statuses = ['Draft', 'Planning', 'In Review', 'Approved', 'Completed', 'Cancelled'];
    const priorities = ['Low', 'Medium', 'High'];

    useEffect(() => {
        if (isEdit) {
            fetchCaseDetails();
        }
    }, [caseId]);

    const fetchCaseDetails = async () => {
        setLoadingCase(true);
        const res = await apiFetch(`/api/cases/${caseId}`);
        if (res.ok && res.data.success) {
            const c = res.data.data;
            setFormData({
                case_number: c.case_number || '',
                patient_reference: c.patient_reference || '',
                surgeon_name: c.surgeon_name || '',
                implant_type: c.implant_type || '',
                status: c.status || 'Draft',
                priority: c.priority || 'Medium',
                surgery_date: c.surgery_date ? c.surgery_date.substring(0, 10) : '',
            });
        } else {
            setGlobalError('Could not load case details for editing.');
        }
        setLoadingCase(false);
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
        if (fieldErrors[name]) {
            setFieldErrors(prev => ({ ...prev, [name]: null }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setFieldErrors({});
        setGlobalError('');

        const endpoint = isEdit ? `/api/cases/${caseId}` : '/api/cases';
        const method = isEdit ? 'PUT' : 'POST';

        const res = await apiFetch(endpoint, {
            method,
            body: formData,
        });

        if (res.ok && res.data.success) {
            const targetId = res.data.data.id || caseId;
            navigate('cases-show', { id: targetId });
        } else {
            if (res.status === 422 && res.data.data?.errors) {
                setFieldErrors(res.data.data.errors);
            } else {
                setGlobalError(res.data.message || 'Error saving case. Please check your inputs.');
            }
        }
        setSubmitting(false);
    };

    if (loadingCase) {
        return (
            <div style={{ padding: '60px', textAlign: 'center', color: 'var(--color-muted)' }}>
                Loading case details...
            </div>
        );
    }

    return (
        <div style={{ maxWidth: '760px', margin: '0 auto' }}>
            <div style={{ marginBottom: '24px' }}>
                <button
                    type="button"
                    onClick={() => isEdit ? navigate('cases-show', { id: caseId }) : navigate('cases')}
                    style={{ background: 'none', border: 'none', color: 'var(--color-muted)', cursor: 'pointer', fontSize: '13px', fontWeight: 500, padding: 0 }}
                >
                    &larr; Back to {isEdit ? 'Case Details' : 'Cases Directory'}
                </button>
                <h1 style={{ marginTop: '8px' }}>
                    {isEdit ? `Edit Case: ${formData.case_number}` : 'New Medical Implant Case'}
                </h1>
                <p style={{ color: 'var(--color-muted)' }}>
                    {isEdit ? 'Update case clinical details, status, or surgery date.' : 'Log a new patient-specific implant case into the tracking registry.'}
                </p>
            </div>

            {globalError && (
                <div className="alert alert-danger" style={{ marginBottom: '24px' }}>
                    <span>{globalError}</span>
                </div>
            )}

            <div className="card">
                <form onSubmit={handleSubmit} id="case-form">
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
                        {/* Case Number */}
                        <div className="form-group">
                            <label htmlFor="case_number" className="form-label">
                                Case Number <span style={{ color: 'var(--color-semantic-down)' }}>*</span>
                            </label>
                            <input
                                type="text"
                                name="case_number"
                                id="case_number"
                                className={`form-control mono ${fieldErrors.case_number ? 'is-invalid' : ''}`}
                                value={formData.case_number}
                                onChange={handleChange}
                                placeholder="e.g. CASE-0001"
                                required
                            />
                            {fieldErrors.case_number && (
                                <span className="error-message">{fieldErrors.case_number[0]}</span>
                            )}
                        </div>

                        {/* Patient Reference */}
                        <div className="form-group">
                            <label htmlFor="patient_reference" className="form-label">
                                Patient Reference Code <span style={{ color: 'var(--color-semantic-down)' }}>*</span>
                            </label>
                            <input
                                type="text"
                                name="patient_reference"
                                id="patient_reference"
                                className={`form-control mono ${fieldErrors.patient_reference ? 'is-invalid' : ''}`}
                                value={formData.patient_reference}
                                onChange={handleChange}
                                placeholder="e.g. PT-4821-XA (dummy code)"
                                required
                            />
                            {fieldErrors.patient_reference && (
                                <span className="error-message">{fieldErrors.patient_reference[0]}</span>
                            )}
                        </div>
                    </div>

                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
                        {/* Surgeon Name */}
                        <div className="form-group">
                            <label htmlFor="surgeon_name" className="form-label">
                                Surgeon Name <span style={{ color: 'var(--color-semantic-down)' }}>*</span>
                            </label>
                            <input
                                type="text"
                                name="surgeon_name"
                                id="surgeon_name"
                                className={`form-control ${fieldErrors.surgeon_name ? 'is-invalid' : ''}`}
                                value={formData.surgeon_name}
                                onChange={handleChange}
                                placeholder="e.g. Dr. Sarah Jenkins"
                                required
                            />
                            {fieldErrors.surgeon_name && (
                                <span className="error-message">{fieldErrors.surgeon_name[0]}</span>
                            )}
                        </div>

                        {/* Implant Type */}
                        <div className="form-group">
                            <label htmlFor="implant_type" className="form-label">
                                Implant Type / Model <span style={{ color: 'var(--color-semantic-down)' }}>*</span>
                            </label>
                            <input
                                type="text"
                                name="implant_type"
                                id="implant_type"
                                className={`form-control ${fieldErrors.implant_type ? 'is-invalid' : ''}`}
                                value={formData.implant_type}
                                onChange={handleChange}
                                placeholder="e.g. Cranial Titanium Mesh 3D"
                                required
                            />
                            {fieldErrors.implant_type && (
                                <span className="error-message">{fieldErrors.implant_type[0]}</span>
                            )}
                        </div>
                    </div>

                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '20px' }}>
                        {/* Status */}
                        <div className="form-group">
                            <label htmlFor="status" className="form-label">
                                Lifecycle Status <span style={{ color: 'var(--color-semantic-down)' }}>*</span>
                            </label>
                            <select
                                name="status"
                                id="status"
                                className={`form-control ${fieldErrors.status ? 'is-invalid' : ''}`}
                                value={formData.status}
                                onChange={handleChange}
                                required
                            >
                                {statuses.map((st) => (
                                    <option key={st} value={st}>{st}</option>
                                ))}
                            </select>
                            {fieldErrors.status && (
                                <span className="error-message">{fieldErrors.status[0]}</span>
                            )}
                        </div>

                        {/* Priority */}
                        <div className="form-group">
                            <label htmlFor="priority" className="form-label">
                                Clinical Priority <span style={{ color: 'var(--color-semantic-down)' }}>*</span>
                            </label>
                            <select
                                name="priority"
                                id="priority"
                                className={`form-control ${fieldErrors.priority ? 'is-invalid' : ''}`}
                                value={formData.priority}
                                onChange={handleChange}
                                required
                            >
                                {priorities.map((pr) => (
                                    <option key={pr} value={pr}>{pr}</option>
                                ))}
                            </select>
                            {fieldErrors.priority && (
                                <span className="error-message">{fieldErrors.priority[0]}</span>
                            )}
                        </div>

                        {/* Surgery Date */}
                        <div className="form-group">
                            <label htmlFor="surgery_date" className="form-label">
                                Surgery Date <span style={{ fontSize: '11px', color: 'var(--color-muted)' }}>(Optional)</span>
                            </label>
                            <input
                                type="date"
                                name="surgery_date"
                                id="surgery_date"
                                className={`form-control mono ${fieldErrors.surgery_date ? 'is-invalid' : ''}`}
                                value={formData.surgery_date}
                                onChange={handleChange}
                            />
                            {fieldErrors.surgery_date && (
                                <span className="error-message">{fieldErrors.surgery_date[0]}</span>
                            )}
                        </div>
                    </div>

                    {/* Action buttons */}
                    <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '12px', marginTop: '24px', paddingTop: '20px', borderTop: '1px solid var(--color-hairline)' }}>
                        <button
                            type="button"
                            className="btn btn-secondary"
                            onClick={() => isEdit ? navigate('cases-show', { id: caseId }) : navigate('cases')}
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            className="btn btn-primary"
                            disabled={submitting}
                        >
                            {submitting ? 'Saving...' : (isEdit ? 'Save Changes' : 'Create Medical Case')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
