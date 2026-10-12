import React from 'react';
import { useAuth } from '../context/AuthContext';

export default function Navbar({ currentView, navigate }) {
    const { user, logout } = useAuth();

    if (!user) return null;

    const canCreate = user.role === 'admin' || user.role === 'engineer';

    return (
        <header className="top-nav">
            <div className="app-container">
                <div className="nav-inner">
                    <a
                        href="#/dashboard"
                        className="brand-logo"
                        onClick={(e) => {
                            e.preventDefault();
                            navigate('dashboard');
                        }}
                    >
                        <span className="brand-icon">M</span>
                        <span>
                            MediTrack{' '}
                            <span style={{ fontSize: '11px', fontWeight: 500, color: 'var(--color-muted)', textTransform: 'uppercase', marginLeft: '4px' }}>
                                @Jajal Medical
                            </span>
                        </span>
                    </a>

                    <nav>
                        <ul className="nav-links">
                            <li>
                                <button
                                    type="button"
                                    className={`nav-link ${currentView === 'dashboard' ? 'active' : ''}`}
                                    onClick={() => navigate('dashboard')}
                                    style={{ background: 'none', border: 'none', cursor: 'pointer' }}
                                >
                                    Dashboard
                                </button>
                            </li>
                            <li>
                                <button
                                    type="button"
                                    className={`nav-link ${currentView === 'cases' ? 'active' : ''}`}
                                    onClick={() => navigate('cases')}
                                    style={{ background: 'none', border: 'none', cursor: 'pointer' }}
                                >
                                    Cases
                                </button>
                            </li>
                            {canCreate && (
                                <li>
                                    <button
                                        type="button"
                                        className="btn btn-primary btn-sm"
                                        onClick={() => navigate('cases-create')}
                                        style={{ height: '36px', padding: '0 16px', cursor: 'pointer' }}
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>New Case</span>
                                    </button>
                                </li>
                            )}
                        </ul>
                    </nav>

                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                        <div className="user-profile-pill">
                            <span style={{ fontWeight: 600, color: 'var(--color-ink)', fontSize: '13px' }}>
                                {user.name}
                            </span>
                            <span className={`badge badge-role-${user.role}`}>
                                {user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                            </span>
                        </div>

                        <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={logout}
                            title="Sign Out"
                            style={{ cursor: 'pointer' }}
                        >
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </header>
    );
}
