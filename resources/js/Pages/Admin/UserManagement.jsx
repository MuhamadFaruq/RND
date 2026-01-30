import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal';

// Update props untuk menerima data kesehatan sistem
export default function UserManagement({ auth, users, audit_logs = [], flash, storage, laravel_version, php_version }) {
    const { data, setData, post, processing, reset, errors } = useForm({
        name: '', 
        email: '', 
        password: '', 
        role: 'operator',
        division: ''
    });

    const [showToast, setShowToast] = useState(false);
    
    const [modalConfig, setModalConfig] = useState({
        isOpen: false,
        type: null, 
        user: null
    });

    useEffect(() => {
        if (flash?.success) {
            setShowToast(true);
            const timer = setTimeout(() => setShowToast(false), 5000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    const [editMode, setEditMode] = useState(false);
    const [selectedUserId, setSelectedUserId] = useState(null);

    const editUser = (user) => {
        setEditMode(true);
        setSelectedUserId(user.id);
        setData({
            name: user.name,
            email: user.email,
            role: user.role,
            division: user.division || '',
            password: '', 
        });
    };

    const submit = (e) => {
        e.preventDefault();
        if (editMode) {
            router.put(route('users.update', selectedUserId), data, {
                onSuccess: () => {
                    reset();
                    setEditMode(false);
                    setSelectedUserId(null);
                }
            });
        } else {
            post(route('users.store'), {
                onSuccess: () => reset()
            });
        }
    };

    const openModal = (type, user) => {
        setModalConfig({ isOpen: true, type, user });
    };

    const handleConfirmAction = () => {
        if (modalConfig.type === 'delete') {
            router.delete(route('users.destroy', modalConfig.user.id), {
                onSuccess: () => setModalConfig({ ...modalConfig, isOpen: false })
            });
        } else if (modalConfig.type === 'reset') {
            router.patch(route('users.reset-password', modalConfig.user.id), {
                onSuccess: () => setModalConfig({ ...modalConfig, isOpen: false })
            });
        }
    };

    return (
        <AuthenticatedLayout 
            user={auth.user} 
            header={
                <div className="flex items-center gap-3">
                    <div className="bg-red-600 p-2 rounded-lg shadow-lg shadow-red-200">
                        <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h2 className="font-black text-xl text-slate-800 uppercase tracking-tighter">Otoritas & Manajemen Akses</h2>
                </div>
            }
        >
            <Head title="User Management" />
            
            <div className="py-12 bg-slate-50 min-h-screen">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {showToast && (
                        <div className="fixed top-24 right-8 z-50 animate-fade-in-right">
                            <div className="bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl border-l-4 border-green-500 flex items-center gap-4">
                                <span className="text-xl">✅</span>
                                <div>
                                    <p className="text-[10px] font-black uppercase text-gray-400">Sistem Informasi</p>
                                    <p className="text-sm font-bold uppercase">{flash.success}</p>
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start relative">
                        {/* LEFT: Form Card */}
                        <div className="lg:col-span-4 lg:sticky lg:top-28 z-20">
                            <div className="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white relative overflow-hidden">
                                <h3 className="text-slate-900 font-black text-lg mb-1 uppercase tracking-tighter italic">Registrasi Akun Staf</h3>
                                <p className="text-[10px] text-slate-400 font-bold uppercase mb-8 leading-tight italic">Pastikan email perusahaan valid dan aktif.</p>
                                
                                <form onSubmit={submit} className="space-y-5 relative z-10">
                                    <div className="group">
                                        <label className="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Personal Name</label>
                                        <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} className="w-full bg-slate-50 rounded-2xl border-none focus:ring-2 focus:ring-red-500 transition-all font-bold text-sm px-5 py-4" placeholder="Nama Lengkap" />
                                        {errors.name && <p className="text-red-600 text-[10px] font-black italic mt-1 uppercase">{errors.name}</p>}
                                    </div>
                                    <div className="group">
                                        <label className="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">Company Email</label>
                                        <input type="email" value={data.email} onChange={e => setData('email', e.target.value)} className="w-full bg-slate-50 rounded-2xl border-none focus:ring-2 focus:ring-red-500 transition-all font-bold text-sm px-5 py-4" placeholder="email@duniatex.com" />
                                        {errors.email && <p className="text-red-600 text-[10px] font-black italic mt-1 uppercase">{errors.email}</p>}
                                    </div>
                                    <div className="group">
                                        <label className="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">{editMode ? 'Password Baru' : 'Password'}</label>
                                        <input type="password" value={data.password} onChange={e => setData('password', e.target.value)} className="w-full bg-slate-50 rounded-2xl border-none focus:ring-2 focus:ring-red-500 transition-all font-bold text-sm px-5 py-4" placeholder="••••••••" />
                                    </div>
                                    <div className="group">
                                        <label className="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-2 block">System Permission</label>
                                        <select value={data.role} onChange={e => setData('role', e.target.value)} className="w-full bg-slate-50 rounded-2xl border-none focus:ring-2 focus:ring-red-500 font-bold text-sm px-5 py-4">
                                            <option value="operator">OPERATOR PRODUKSI</option>
                                            <option value="marketing">TIM MARKETING</option>
                                            <option value="superadmin">SUPER ADMINISTRATOR</option>
                                        </select>
                                    </div>

                                    {/* INPUT DIVISI DYNAMIC */}
                                    {data.role === 'operator' && (
                                        <div className="group animate-in fade-in slide-in-from-top-2">
                                            <label className="text-[9px] font-black text-red-500 uppercase tracking-widest ml-1 mb-2 block italic">Penempatan Divisi</label>
                                            <select value={data.division} onChange={e => setData('division', e.target.value)} className="w-full bg-red-50 rounded-2xl border-none focus:ring-2 focus:ring-red-500 font-bold text-sm px-5 py-4 uppercase">
                                                <option value="">-- PILIH DIVISI --</option>
                                                <option value="knitting">KNITTING (RAJUT)</option>
                                                <option value="dyeing">DYEING (WARNA)</option>
                                                <option value="stenter">STENTER (FINISHING)</option>
                                                <option value="qc">QUALITY CONTROL (QC)</option>
                                            </select>
                                        </div>
                                    )}

                                    <div className="flex flex-col gap-3 pt-4">
                                        <button disabled={processing} className={`w-full py-5 rounded-2xl font-black text-xs uppercase tracking-widest text-white shadow-xl ${editMode ? 'bg-blue-600' : 'bg-red-600'}`}>
                                            {processing ? 'Memproses...' : editMode ? 'Simpan Perubahan' : 'Aktivasi Akun'}
                                        </button>
                                        {editMode && (
                                            <button type="button" onClick={() => { setEditMode(false); reset(); }} className="py-2 text-[10px] font-black uppercase text-slate-400 italic">Batal Edit</button>
                                        )}
                                    </div>
                                </form>
                            </div>
                        </div>

                        {/* RIGHT: User List */}
                        <div className="lg:col-span-8 space-y-4">
                            <div className="bg-slate-900 p-6 rounded-[2rem] shadow-2xl flex justify-between items-center mb-6">
                                <div>
                                    <p className="text-[10px] text-gray-400 font-black uppercase tracking-[0.3em]">Status Staf Aktif</p>
                                    <h4 className="text-white font-black text-xl uppercase tracking-tighter italic">Daftar Pengguna Sistem</h4>
                                </div>
                                <div className="bg-white/10 px-4 py-2 rounded-xl border border-white/10">
                                    <span className="text-red-500 font-black text-xl">{users.length}</span>
                                    <span className="text-white/50 text-[10px] font-bold uppercase ml-2">Total Users</span>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 uppercase">
                                {users.map(user => (
                                    <div key={user.id} className="group bg-white p-6 rounded-[2rem] border border-slate-100 hover:border-red-200 transition-all flex items-center justify-between">
                                        <div className="flex items-center gap-4">
                                            <div className="relative">
                                                <div className={`w-12 h-12 rounded-2xl flex items-center justify-center text-lg font-black ${user.role === 'superadmin' ? 'bg-slate-900 text-white' : 'bg-red-50 text-red-600'}`}>
                                                    {user.name.charAt(0)}
                                                </div>
                                                {/* ACTIVITY MONITORING INDICATOR (POIN 2) */}
                                                <div className={`absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-4 border-white ${
                                                    new Date(user.last_seen) > new Date(Date.now() - 5 * 60000) ? 'bg-green-500 animate-pulse' : 'bg-slate-300'
                                                }`}></div>
                                            </div>
                                            <div>
                                                <p className="text-slate-900 text-sm font-black tracking-tighter leading-none mb-1">{user.name}</p>
                                                <p className="text-slate-400 lowercase font-bold text-[9px] mb-2">{user.email}</p>
                                                <span className={`px-2 py-0.5 rounded text-[8px] font-black text-white uppercase italic ${user.role === 'superadmin' ? 'bg-slate-900' : 'bg-red-600'}`}>
                                                    {user.role} {user.division ? `| ${user.division}` : ''}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            {user.id !== auth.user.id && (
                                                <>
                                                    <button onClick={() => editUser(user)} className="text-[9px] font-black text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg">EDIT</button>
                                                    <button onClick={() => openModal('reset', user)} className="text-[9px] font-black text-orange-600 bg-orange-50 px-3 py-1.5 rounded-lg">RESET</button>
                                                    <button onClick={() => openModal('delete', user)} className="text-[9px] font-black text-red-600 bg-red-50 px-3 py-1.5 rounded-lg">HAPUS</button>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* SECTION: AUDIT LOGS & BACKUP */}
                    <div className="mt-12 bg-white rounded-[2.5rem] p-10 shadow-xl border border-white">
                        <div className="flex items-center justify-between mb-8 border-b border-slate-50 pb-6">
                            <div className="flex items-center gap-4">
                                <div className="bg-slate-900 p-3 rounded-2xl shadow-lg">
                                    <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 className="text-slate-900 font-black text-xl uppercase tracking-tighter italic leading-none">System Audit Logs</h3>
                                    <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Transparansi Aktivitas & Keamanan Data</p>
                                </div>
                            </div>
                            
                            {/* BACKUP DATABASE BUTTON (POIN 3) */}
                            <button onClick={() => window.open(route('admin.backup'), '_blank')} className="group flex items-center gap-3 bg-green-50 hover:bg-green-600 px-6 py-3 rounded-2xl transition-all">
                                <span className="text-lg">💾</span>
                                <div className="text-left">
                                    <p className="text-[8px] font-black text-green-600 group-hover:text-white uppercase leading-none mb-1">Database Security</p>
                                    <p className="text-[10px] font-black text-green-800 group-hover:text-white uppercase tracking-tighter leading-none">Download Backup SQL</p>
                                </div>
                            </button>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-separate border-spacing-y-2">
                                <thead>
                                    <tr className="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        <th className="px-4 py-3">Timestamp</th>
                                        <th className="px-4 py-3">Administrator</th>
                                        <th className="px-4 py-3">Aktivitas</th>
                                        <th className="px-4 py-3">Deskripsi</th>
                                        <th className="px-4 py-3 text-right">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {audit_logs.map((log) => (
                                        <tr key={log.id} className="bg-slate-50 hover:bg-slate-100">
                                            <td className="px-4 py-4 rounded-l-2xl text-[10px] font-bold text-slate-500">{new Date(log.created_at).toLocaleString('id-ID')}</td>
                                            <td className="px-4 py-4 font-black text-slate-900 text-[11px] uppercase italic">{log.user?.name || 'SYSTEM'}</td>
                                            <td className="px-4 py-4">
                                                <span className={`px-2 py-0.5 rounded text-[8px] font-black uppercase ${log.action.includes('DELETE') ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'}`}>
                                                    {log.action}
                                                </span>
                                            </td>
                                            <td className="px-4 py-4 text-[11px] font-medium text-slate-600 italic">{log.details}</td>
                                            <td className="px-4 py-4 rounded-r-2xl text-[9px] font-black text-slate-400 text-right font-mono">{log.ip_address}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* SECTION: SYSTEM HEALTH DASHBOARD (POIN 5) */}
                    <div className="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 animate-in fade-in duration-1000 pb-20">
                        <div className="bg-white p-6 rounded-[2rem] shadow-xl border border-white relative overflow-hidden">
                            <div className="absolute top-0 right-0 p-4 opacity-5">💾</div>
                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Storage Capacity</p>
                            <div className="flex items-end gap-2">
                                <span className="text-3xl font-black text-slate-900 italic leading-none">{storage?.percentage || 0}%</span>
                                <span className="text-[9px] font-bold text-slate-400 uppercase mb-1 italic">Used of {storage?.total || 0} GB</span>
                            </div>
                            <div className="w-full bg-slate-100 h-2 rounded-full mt-4 overflow-hidden border border-slate-50">
                                <div className={`h-full transition-all duration-1000 ${storage?.percentage > 85 ? 'bg-red-600' : 'bg-green-500'}`} style={{ width: `${storage?.percentage || 0}%` }}></div>
                            </div>
                        </div>
                        
                        <div className="bg-white p-6 rounded-[2rem] shadow-xl border border-white">
                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Environment Status</p>
                            <div className="space-y-2">
                                <div className="flex justify-between items-center bg-slate-50 p-2 rounded-xl">
                                    <span className="text-[9px] font-black text-slate-400 uppercase">Laravel</span>
                                    <span className="text-[10px] font-black text-slate-900">v{laravel_version || 'N/A'}</span>
                                </div>
                                <div className="flex justify-between items-center bg-slate-50 p-2 rounded-xl">
                                    <span className="text-[9px] font-black text-slate-400 uppercase">PHP Version</span>
                                    <span className="text-[10px] font-black text-slate-900">{php_version || 'N/A'}</span>
                                </div>
                            </div>
                        </div>

                        <div className="bg-slate-900 p-6 rounded-[2rem] shadow-xl flex flex-col justify-center text-center">
                            <p className="text-[9px] font-black text-green-500 uppercase tracking-[0.3em] mb-1 animate-pulse italic">● System Online</p>
                            <h4 className="text-white font-black text-sm uppercase italic tracking-tighter">Manufacturing Node Stable</h4>
                        </div>
                    </div>
                </div>
            </div>

            <ConfirmDeleteModal 
                isOpen={modalConfig.isOpen} 
                onClose={() => setModalConfig({ ...modalConfig, isOpen: false })} 
                onConfirm={handleConfirmAction} 
                itemTitle={
                    modalConfig.type === 'reset' 
                    ? `Password ${modalConfig.user?.name} ke default (duniatex123)` 
                    : `Akses Akun ${modalConfig.user?.name}`
                } 
            />
        </AuthenticatedLayout>
    );
}