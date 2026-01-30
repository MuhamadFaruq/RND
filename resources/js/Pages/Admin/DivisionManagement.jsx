import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Swal from 'sweetalert2';

export default function DivisionManagement({ auth, divisions }) {
    const [isEditing, setIsEditing] = useState(false);
    const [editId, setEditId] = useState(null);

    const { data, setData, post, put, delete: destroy, processing, reset, errors } = useForm({
        name: '',
        slug: '',
        icon: '',
    });

    // Fungsi untuk memicu Mode Edit
    const startEdit = (div) => {
        setIsEditing(true);
        setEditId(div.id);
        setData({ name: div.name, slug: div.slug, icon: div.icon });
    };

    const cancelEdit = () => {
        setIsEditing(false);
        reset();
    };

    const submit = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('admin.divisions.update', editId), {
                onSuccess: () => { cancelEdit(); Swal.fire('Updated!', 'Divisi berhasil diperbarui.', 'success'); }
            });
        } else {
            post(route('admin.divisions.store'), {
                onSuccess: () => { reset(); Swal.fire('Success!', 'Divisi berhasil ditambah.', 'success'); }
            });
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Manajemen Divisi" />
            <div className="py-12 bg-slate-50 min-h-screen">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 flex gap-8">
                    
                    {/* FORM CARD (DINAMIS: TAMBAH / EDIT) */}
                    <div className="w-1/3 bg-white p-8 rounded-[2.5rem] shadow-xl border-t-8 border-red-600 h-fit sticky top-24">
                        <h2 className="text-xl font-black uppercase italic mb-6">
                            {isEditing ? '📝 Edit Data Divisi' : '➕ Tambah Divisi Baru'}
                        </h2>
                        <form onSubmit={submit} className="space-y-4">
                            <input type="text" className="w-full rounded-2xl border-slate-200" value={data.name} 
                                onChange={e => setData('name', e.target.value)} placeholder="Nama Divisi" />
                            <input type="text" className="w-full rounded-2xl border-slate-200" value={data.slug} 
                                onChange={e => setData('slug', e.target.value)} placeholder="slug (contoh: qc)" />
                            <input type="text" className="w-full rounded-2xl border-slate-200 text-center text-4xl" value={data.icon} 
                                onChange={e => setData('icon', e.target.value)} placeholder="Icon (Emoji)" />
                            
                            <button className="w-full bg-red-600 text-white font-black py-4 rounded-2xl uppercase italic">
                                {isEditing ? 'Update Divisi' : 'Simpan Divisi'}
                            </button>
                            {isEditing && (
                                <button type="button" onClick={cancelEdit} className="w-full bg-slate-100 text-slate-500 font-bold py-2 rounded-xl mt-2 uppercase text-[10px]">
                                    Batal Edit
                                </button>
                            )}
                        </form>
                    </div>

                    {/* LIST CARD (Kompak & Modern) */}
                    <div className="w-2/3 grid grid-cols-1 gap-4">
                        {divisions.map((div) => (
                            <div key={div.id} className="bg-white p-6 rounded-[2rem] shadow-md flex items-center justify-between group border border-transparent hover:border-red-200 transition-all">
                                <div className="flex items-center gap-6">
                                    <div className="text-4xl bg-slate-50 w-16 h-16 flex items-center justify-center rounded-2xl shadow-inner group-hover:scale-110 transition-transform">
                                        {div.icon}
                                    </div>
                                    <div>
                                        <h3 className="font-black uppercase italic text-lg leading-none">{div.name}</h3>
                                        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Slug: {div.slug}</p>
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    <button onClick={() => startEdit(div)} className="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl font-bold text-[10px] uppercase hover:bg-blue-600 hover:text-white transition-all">Edit</button>
                                    <button onClick={() => destroy(route('admin.divisions.destroy', div.id))} className="bg-red-50 text-red-600 px-4 py-2 rounded-xl font-bold text-[10px] uppercase hover:bg-red-600 hover:text-white transition-all">Hapus</button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}