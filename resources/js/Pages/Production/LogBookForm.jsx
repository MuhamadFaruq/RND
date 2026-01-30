import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Swal from 'sweetalert2'; // Tambahkan untuk notifikasi cantik

// --- IMPORT SEMUA FORM ---
import KnittingForm from '../Operator/Forms/KnittingForm';
import DyeingForm from '../Operator/Forms/DyeingForm';
import StenterForm from '../Operator/Forms/StenterForm';
import CompactorForm from '../Operator/Forms/CompactorForm';
import HeatSettingForm from '../Operator/Forms/HeatSettingForm';
import RelaxDryerForm from '../Operator/Forms/RelaxDryerForm';
import TumblerForm from '../Operator/Forms/TumblerForm';
import FleeceForm from '../Operator/Forms/FleeceForm';
import QEForm from '../Operator/Forms/QEForm';
import PengujianForm from '../Operator/Forms/PengujianForm';

export default function LogBookForm(props) {
    const { auth, division } = props;
    
    // State untuk status loading pencarian SAP
    const [isSearching, setIsSearching] = useState(false);

    const getDefaultSubProcess = () => {
        if (division?.toLowerCase() === 'qc') return 'qe';
        return 'stenter';
    };

    const [subProcess, setSubProcess] = useState(getDefaultSubProcess());

    const { data, setData, post, processing, reset } = useForm({
        tanggal_mesin: props.tanggal_mesin || '',
        no_mesin: '',
        sap_no: '',
        division_name: division || '',
        sub_process: getDefaultSubProcess(),
        // Field otomatis dari SAP:
        pelanggan: '',
        warna: '',
        jenis_kain: '',
    });

    // --- FUNGSI PENCARIAN SAP OTOMATIS ---
    const fetchSapDetail = async (sapNo) => {
        if (!sapNo || sapNo.length < 4) return;
        
        setIsSearching(true);
        try {
            const response = await fetch(`/api/marketing-orders/${sapNo}`);
            const result = await response.json();
            
            if (result.ok) {
                // Isi otomatis state data dengan detail dari database
                setData({
                    ...data,
                    sap_no: sapNo,
                    pelanggan: result.marketing_order.pelanggan,
                    warna: result.marketing_order.warna,
                    jenis_kain: result.marketing_order.art_no,
                    // Tambahkan mapping field lain jika perlu
                });
                
                // Toast sukses (opsional)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Data SAP Ditemukan',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                Swal.fire('Error', 'Nomor SAP tidak ditemukan!', 'error');
            }
        } catch (error) {
            console.error("Gagal menarik data SAP:", error);
        } finally {
            setIsSearching(false);
        }
    };

    const handleInputChange = (key, value) => {
        setData(key, value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('log.store'), {
            onSuccess: () => {
                Swal.fire('Berhasil!', 'Data Produksi Telah Tersimpan', 'success');
                reset();
            },
        });
    };

    const renderForm = () => {
        // Kirimkan fetchSapDetail dan isSearching ke semua form anak
        const formProps = {
            formData: data || {}, 
            onInputChange: handleInputChange,
            onSubmit: handleSubmit,
            fetchSapDetail: fetchSapDetail, // Fungsi ini sekarang bisa dipanggil di KnittingForm, dsb.
            isSearching: isSearching,
            processing: processing,
            ...props 
        };

        const div = division?.toLowerCase();

        if (div === 'knitting') return <KnittingForm {...formProps} />;
        if (div === 'dyeing') return <DyeingForm {...formProps} />;
        
        if (div === 'stenter') {
            return (
                <div className="space-y-6">
                    <p className="text-[10px] font-black uppercase text-slate-400 italic mb-2 ml-2">Jalur Mesin Finishing:</p>
                    <div className="flex flex-wrap gap-2 p-2 bg-slate-100 rounded-2xl border border-slate-200">
                        {['stenter', 'compactor', 'heatsetting', 'relaxdryer', 'tumbler', 'fleece'].map((proc) => (
                            <button key={proc} type="button" onClick={() => {setSubProcess(proc); setData('sub_process', proc)}}
                                className={`flex-1 min-w-[100px] px-4 py-3 rounded-xl text-[9px] font-black uppercase italic transition-all duration-300 ${subProcess === proc ? 'bg-red-600 text-white shadow-lg scale-105' : 'bg-white text-slate-400 hover:text-red-500'}`}>
                                {proc}
                            </button>
                        ))}
                    </div>
                    {subProcess === 'stenter' && <StenterForm {...formProps} />}
                    {subProcess === 'compactor' && <CompactorForm {...formProps} />}
                    {subProcess === 'heatsetting' && <HeatSettingForm {...formProps} />}
                    {subProcess === 'relaxdryer' && <RelaxDryerForm {...formProps} />}
                    {subProcess === 'tumbler' && <TumblerForm {...formProps} />}
                    {subProcess === 'fleece' && <FleeceForm {...formProps} />}
                </div>
            );
        }

        if (div === 'qc' || div === 'qe') {
            return (
                <div className="space-y-6">
                    <p className="text-[10px] font-black uppercase text-blue-400 italic mb-2 ml-2">Mode Pengujian Lab:</p>
                    <div className="flex gap-2 p-2 bg-blue-50 rounded-2xl border border-blue-100">
                        {['qe', 'pengujian'].map((proc) => (
                            <button key={proc} type="button" onClick={() => {setSubProcess(proc); setData('sub_process', proc)}}
                                className={`flex-1 px-4 py-3 rounded-xl text-[10px] font-black uppercase italic transition-all duration-300 ${subProcess === proc ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-blue-400 hover:bg-blue-100'}`}>
                                {proc === 'qe' ? 'Quality Evaluation' : 'Pengujian Fisik'}
                            </button>
                        ))}
                    </div>
                    {subProcess === 'qe' && <QEForm {...formProps} />}
                    {subProcess === 'pengujian' && <PengujianForm {...formProps} />}
                </div>
            );
        }

        return <div className="p-10 text-center font-bold text-red-500 italic">DIVISI "{division}" TIDAK TERDAFTAR</div>;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-black text-xl text-slate-800 uppercase italic leading-tight">Log Book: <span className="text-red-600">{division}</span></h2>}
        >
            <Head title={`Log Book ${division}`} />
            <div className="py-12 bg-slate-50 min-h-screen">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-2xl rounded-[3rem] p-6 border-b-[12px] border-red-600">
                        {renderForm()}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}