import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';

export default function LoginScreen({ onLoginSuccess }) {
    const { login, loading } = useAuth();
    const [email, setEmail] = useState('admin@jajalmedical.com');
    const [password, setPassword] = useState('Password123!');
    const [errorMessage, setErrorMessage] = useState('');
    const [fieldErrors, setFieldErrors] = useState({});

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrorMessage('');
        setFieldErrors({});

        const result = await login(email, password);

        if (result.success) {
            if (onLoginSuccess) onLoginSuccess();
        } else {
            setErrorMessage(result.message);
            if (result.errors) {
                setFieldErrors(result.errors);
            }
        }
    };

    const setCredentials = (userEmail) => {
        setEmail(userEmail);
        setPassword('Password123!');
        setErrorMessage('');
        setFieldErrors({});
    };

    return (
        <div style={{ maxWidth: '460px', margin: '40px auto' }}>
            <div className="card" style={{ padding: '40px 36px' }}>
                <div style={{ textAlign: 'center', marginBottom: '32px' }}>
                    <div style={{
                        width: '48px',
                        height: '48px',
                        borderRadius: '9999px',
                        backgroundColor: 'var(--color-primary)',
                        color: '#fff',
                        display: 'inline-flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: '24px',
                        fontWeight: 700,
                        marginBottom: '16px',
                    }}>
                        M
                    </div>
                    <h1 style={{ fontSize: '26px', fontWeight: 500, color: 'var(--color-ink)', marginBottom: '8px' }}>
                        Sign in to MediTrack
                    </h1>
                    <p style={{ color: 'var(--color-muted)', fontSize: '14px' }}>
                        Clinical Case Management &bull; @Jajal Medical
                    </p>
                </div>

                {errorMessage && (
                    <div className="alert alert-danger" style={{ marginBottom: '24px' }}>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>{errorMessage}</span>
                    </div>
                )}

                <form onSubmit={handleSubmit} id="react-login-form">
                    <div className="form-group">
                        <label htmlFor="email" className="form-label">Work Email Address</label>
                        <input
                            type="email"
                            id="email"
                            className={`form-control ${fieldErrors.email ? 'is-invalid' : ''}`}
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                            placeholder="name@jajalmedical.com"
                        />
                        {fieldErrors.email && (
                            <span className="error-message">{fieldErrors.email[0]}</span>
                        )}
                    </div>

                    <div className="form-group" style={{ marginBottom: '24px' }}>
                        <label htmlFor="password" className="form-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            className={`form-control ${fieldErrors.password ? 'is-invalid' : ''}`}
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                            placeholder="Enter password"
                        />
                        {fieldErrors.password && (
                            <span className="error-message">{fieldErrors.password[0]}</span>
                        )}
                    </div>

                    <button
                        type="submit"
                        className="btn btn-primary"
                        disabled={loading}
                        style={{ width: '100%', height: '48px', fontSize: '15px' }}
                    >
                        {loading ? 'Authenticating...' : 'Sign In'}
                    </button>
                </form>

                {/* Reviewer Quick-Login Shortcuts */}
                <div style={{ marginTop: '32px', paddingTop: '24px', borderTop: '1px solid var(--color-hairline)' }}>
                    <div style={{ fontSize: '12px', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--color-muted)', marginBottom: '12px', textAlign: 'center' }}>
                        Quick Test Accounts for Reviewers
                    </div>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                        <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={() => setCredentials('admin@jajalmedical.com')}
                            style={{ justifyContent: 'space-between', width: '100%' }}
                        >
                            <span><strong>Admin:</strong> admin@jajalmedical.com</span>
                            <span className="badge badge-role-admin">Full Access</span>
                        </button>
                        <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={() => setCredentials('viewer@jajalmedical.com')}
                            style={{ justifyContent: 'space-between', width: '100%' }}
                        >
                            <span><strong>Viewer:</strong> viewer@jajalmedical.com</span>
                            <span className="badge badge-role-viewer">Read Only</span>
                        </button>
                        <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={() => setCredentials('engineer@jajalmedical.com')}
                            style={{ justifyContent: 'space-between', width: '100%' }}
                        >
                            <span><strong>Engineer:</strong> engineer@jajalmedical.com</span>
                            <span className="badge badge-role-engineer">Bonus Role</span>
                        </button>
                    </div>
                    <p style={{ textAlign: 'center', fontSize: '11px', color: 'var(--color-muted)', marginTop: '10px' }}>
                        Default Password: <code className="mono" style={{ background: 'var(--color-surface-strong)', padding: '2px 6px', borderRadius: '4px' }}>Password123!</code>
                    </p>
                </div>
            </div>
        </div>
    );
}
