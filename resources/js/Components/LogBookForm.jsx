import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import Toast from '@/Components/Toast';

export default function LogBookForm({ division }) {
    const [sapNo, setSapNo] = useState('');
    const [marketingOrder, setMarketingOrder] = useState(null);
    const [loading, setLoading] = useState(false);
    const [searching, setSearching] = useState(false);
    const [showConfirmModal, setShowConfirmModal] = useState(false);
    const [showToast, setShowToast] = useState(false);
    const [technicalData, setTechnicalData] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Fungsi untuk mengecek apakah nilai aktual masuk dalam batas toleransi
    const checkTolerance = (fieldName, actualValue) => {
        if (!marketingOrder || !actualValue) return { isSafe: true };

        let target = 0;
        let tolerance = 0;
        let unit = "";

        if (fieldName === 'actual_width') {
            target = parseFloat(marketingOrder.target_lebar);
            tolerance = 2; // Toleransi +/- 2 cm
            unit = "cm";
        } else if (fieldName === 'actual_gsm') {
            target = parseFloat(marketingOrder.target_gramasi);
            tolerance = 5; // Toleransi +/- 5 gsm
            unit = "gsm";
        } else {
            return { isSafe: true };
        }

        const diff = Math.abs(target - parseFloat(actualValue));
        const isSafe = diff <= tolerance;

        return {
            isSafe,
            message: !isSafe ? `⚠️ Peringatan: Selisih ${diff} ${unit} dari target (${target} ${unit})!` : ""
        };
    };

    // Division-specific field configurations
    const getDivisionFields = () => {
        const div = division.toLowerCase();
        
        if (div.includes('knitting') || div.includes('rajut')) {
            return {
                machine: { label: 'Machine Details', type: 'text', placeholder: 'e.g., KNT-01' },
                actual_width: { label: 'Actual Width (cm)', type: 'number', placeholder: 'e.g., 180' },
                actual_gsm: { label: 'Actual GSM', type: 'number', placeholder: 'e.g., 180' },
                actual_kg: { label: 'Actual KG', type: 'number', placeholder: 'e.g., 150' },
                actual_roll: { label: 'Actual Roll', type: 'number', placeholder: 'e.g., 25' },
                yarn_usage: { label: 'Yarn Usage', type: 'text', placeholder: 'Yarn details' },
            };
        }
        
        if (div.includes('dyeing') || div.includes('warna') || div.includes('scr')) {
            return {
                dye_system: { label: 'Dye System', type: 'text', placeholder: 'e.g., Reactive Dye' },
                color_code: { label: 'Color Code', type: 'text', placeholder: 'e.g., NAVY-001' },
                temperature: { label: 'Temperature (°C)', type: 'number', placeholder: 'e.g., 85' },
                speed: { label: 'Speed (rpm)', type: 'number', placeholder: 'e.g., 120' },
                ph_level: { label: 'pH Level', type: 'number', step: '0.1', placeholder: 'e.g., 7.5' },
                actual_color: { label: 'Actual Color Match', type: 'text', placeholder: 'Color description' },
            };
        }
        
        if (div.includes('stenter')) {
            return {
                preset_temp: { label: 'Preset Temp (°C)', type: 'number', placeholder: 'e.g., 180', group: 'preset' },
                preset_speed: { label: 'Preset Speed (m/min)', type: 'number', placeholder: 'e.g., 30', group: 'preset' },
                drying_temp: { label: 'Drying Temp (°C)', type: 'number', placeholder: 'e.g., 160', group: 'drying' },
                drying_speed: { label: 'Drying Speed (m/min)', type: 'number', placeholder: 'e.g., 25', group: 'drying' },
                finishing_temp: { label: 'Finishing Temp (°C)', type: 'number', placeholder: 'e.g., 170', group: 'finishing' },
                finishing_speed: { label: 'Finishing Speed (m/min)', type: 'number', placeholder: 'e.g., 28', group: 'finishing' },
                overfeed: { label: 'Overfeed (%)', type: 'number', step: '0.1', placeholder: 'e.g., 2.5', group: 'finishing' },
            };
        }
        
        return {
            notes: { label: 'Production Notes', type: 'textarea', placeholder: 'Enter production details...' },
            actual_values: { label: 'Actual Values', type: 'text', placeholder: 'Key measurements' },
        };
    };

    const divisionFields = getDivisionFields();
    const isStenter = division.toLowerCase().includes('stenter');

    const handleSapSearch = async () => {
        if (!sapNo || sapNo.trim() === '') return;
        setSearching(true);
        try {
            const response = await axios.get(`/api/order-details/${sapNo.trim()}`);
            if (response.data.ok && response.data.marketing_order) {
                setMarketingOrder(response.data.marketing_order);
            }
        } catch (error) {
            console.error('Error fetching order:', error);
            alert('Order not found. Please check the SAP number.');
            setMarketingOrder(null);
        } finally {
            setSearching(false);
        }
    };

    const handleFieldChange = (fieldName, value) => {
        setTechnicalData(prev => ({ ...prev, [fieldName]: value }));
    };

    const handleSubmit = async () => {
        if (!marketingOrder) return;
        setSubmitting(true);
        try {
            await axios.post('/production/logs', {
                sap_no: parseInt(sapNo),
                division_name: division,
                technical_data: technicalData,
            });
            setShowConfirmModal(false);
            setShowToast(true);
            setTimeout(() => { router.visit(route('operator.divisions')); }, 1500);
        } catch (error) {
            alert('Failed to submit log.');
        } finally {
            setSubmitting(false);
        }
    };

    const renderField = (fieldName, config) => {
        const value = technicalData[fieldName] || '';
        const toleranceCheck = checkTolerance(fieldName, value);
        
        if (config.type === 'textarea') {
            return (
                <div key={fieldName} className="col-span-full">
                    <label className="block text-sm font-medium text-gray-700 mb-1">{config.label}</label>
                    <textarea
                        value={value}
                        onChange={(e) => handleFieldChange(fieldName, e.target.value)}
                        placeholder={config.placeholder}
                        rows={3}
                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                    />
                </div>
            );
        }

        return (
            <div key={fieldName} className={isStenter && config.group ? '' : 'col-span-1'}>
                <label className="block text-sm font-medium text-gray-700 mb-1">{config.label}</label>
                <TextInput
                    type={config.type || 'text'}
                    value={value}
                    onChange={(e) => handleFieldChange(fieldName, e.target.value)}
                    placeholder={config.placeholder}
                    className={`w-full ${!toleranceCheck.isSafe ? 'border-red-500 ring-1 ring-red-500' : ''}`}
                />
                {!toleranceCheck.isSafe && (
                    <p className="mt-1 text-[10px] font-bold text-red-600 animate-pulse uppercase italic">{toleranceCheck.message}</p>
                )}
            </div>
        );
    };

    const renderStenterFields = () => {
        const presetFields = Object.entries(divisionFields).filter(([_, config]) => config.group === 'preset');
        const dryingFields = Object.entries(divisionFields).filter(([_, config]) => config.group === 'drying');
        const finishingFields = Object.entries(divisionFields).filter(([_, config]) => config.group === 'finishing');

        return (
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-gray-900 border-b pb-2">Preset</h3>
                    {presetFields.map(([fieldName, config]) => renderField(fieldName, config))}
                </div>
                <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-gray-900 border-b pb-2">Drying</h3>
                    {dryingFields.map(([fieldName, config]) => renderField(fieldName, config))}
                </div>
                <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-gray-900 border-b pb-2">Finishing</h3>
                    {finishingFields.map(([fieldName, config]) => renderField(fieldName, config))}
                </div>
            </div>
        );
    };

    return (
        <>
            <Toast
                message="Production log submitted successfully!"
                type="success"
                show={showToast}
                onClose={() => setShowToast(false)}
            />

            {/* SAP Search Section */}
            <div className="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900 italic uppercase">Cari Order Marketing</h3>
                <div className="flex gap-3">
                    <div className="flex-1">
                        <TextInput
                            type="number"
                            value={sapNo}
                            onChange={(e) => setSapNo(e.target.value)}
                            onKeyPress={(e) => e.key === 'Enter' && handleSapSearch()}
                            placeholder="Input Nomor SAP..."
                            className="w-full text-lg font-bold"
                        />
                    </div>
                    <PrimaryButton
                        onClick={handleSapSearch}
                        disabled={searching || !sapNo.trim()}
                        className="bg-red-600 hover:bg-red-700 px-10"
                    >
                        {searching ? 'Loading...' : 'CARI'}
                    </PrimaryButton>
                </div>
            </div>

            {/* FULL DETAIL REFERENCE (THE EXECUTIVE BOX) - OPTIMIZED VERSION */}
            {/* TARGET REFERENCE BOX - FULL DETAIL VERSION */}
{marketingOrder && (
    <div className="mb-10 animate-in fade-in duration-500">
        <div className="bg-[#1a202c] rounded-[2rem] p-8 shadow-2xl relative overflow-hidden border-l-[10px] border-[#ED1C24]">
            {/* Watermark Logo */}
            <div className="absolute top-0 right-0 p-6 opacity-5 font-black text-8xl italic leading-none text-white select-none tracking-tighter">
                DUNIATEX
            </div>
            
            <div className="relative z-10">
                {/* Header: Customer & SAP */}
                <div className="flex justify-between items-start mb-8 border-b border-white/10 pb-6">
                    <div>
                        <h2 className="text-[#ED1C24] font-black text-[10px] uppercase tracking-[0.4em] mb-2 italic">
                            Target Spesifikasi Marketing
                        </h2>
                        <h1 className="text-white text-4xl font-black uppercase tracking-tighter italic leading-none">
                            {marketingOrder.pelanggan}
                        </h1>
                    </div>
                    <div className="text-right">
                        <span className="bg-[#ED1C24] text-white px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest italic shadow-lg">
                            SAP #{marketingOrder.sap_no}
                        </span>
                    </div>
                </div>

                {/* Grid Informasi Detail (Semua Data Marketing) */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-y-8 gap-x-6">
                    {/* Kolom 1: Artikel & Konstruksi */}
                    <div className="border-l border-white/10 pl-5">
                        <p className="text-[9px] text-gray-500 uppercase font-black mb-2 tracking-widest">Artikel / Konstruksi</p>
                        <p className="text-white text-sm font-bold uppercase">{marketingOrder.art_no}</p>
                        <p className="text-blue-400 text-[11px] font-bold uppercase italic mt-1">
                            {marketingOrder.konstruksi_greige || 'No Construction Data'}
                        </p>
                    </div>

                    {/* Kolom 2: Target Lebar */}
                    <div className="border-l border-white/10 pl-5">
                        <p className="text-[9px] text-gray-500 uppercase font-black mb-2 tracking-widest">Target Lebar</p>
                        <p className="text-[#ED1C24] text-3xl font-black italic tracking-tighter leading-none">
                            {marketingOrder.target_lebar} <span className="text-[10px] italic">CM</span>
                        </p>
                        <p className="text-gray-400 text-[10px] uppercase font-bold mt-1">Type: {marketingOrder.belah_bulat}</p>
                    </div>

                    {/* Kolom 3: Target Gramasi */}
                    <div className="border-l border-white/10 pl-5">
                        <p className="text-[9px] text-gray-500 uppercase font-black mb-2 tracking-widest">Target Gramasi</p>
                        <p className="text-[#ED1C24] text-3xl font-black italic tracking-tighter leading-none">
                            {marketingOrder.target_gramasi} <span className="text-[10px] italic">GSM</span>
                        </p>
                        <p className="text-gray-400 text-[10px] uppercase font-bold mt-1 italic">Warna: {marketingOrder.warna}</p>
                    </div>

                    {/* Kolom 4: Material & Quantity */}
                    <div className="border-l border-white/10 pl-5">
                        <p className="text-[9px] text-gray-500 uppercase font-black mb-2 tracking-widest">Material / Target Qty</p>
                        <p className="text-yellow-500 text-xs font-black uppercase leading-tight mb-1">
                            {marketingOrder.material || '-'}
                        </p>
                        <p className="text-green-500 text-sm font-black uppercase italic leading-none">
                            {marketingOrder.roll_target} R / {marketingOrder.kg_target} KG
                        </p>
                    </div>
                </div>

                {/* Catatan Khusus Marketing */}
                {marketingOrder.keterangan_artikel && (
                    <div className="mt-8 p-4 bg-white/5 rounded-2xl border border-white/10">
                        <p className="text-[8px] text-[#ED1C24] uppercase font-black mb-1 italic tracking-widest">
                            ⚠️ Instruksi Khusus Marketing:
                        </p>
                        <p className="text-xs text-gray-300 italic normal-case leading-relaxed font-medium">
                            {marketingOrder.keterangan_artikel}
                        </p>
                    </div>
                )}
            </div>
        </div>
    </div>
)}

            {/* Division-Specific Form Fields */}
            {marketingOrder && (
                <div className="mb-8 rounded-3xl border border-gray-100 bg-white p-10 shadow-2xl animate-in slide-in-from-bottom duration-500">
                    <div className="flex items-center gap-3 mb-8 border-b border-gray-100 pb-4">
                        <div className="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center font-black text-white italic">!</div>
                        <div>
                            <h3 className="text-xl font-black text-gray-900 uppercase italic leading-none">Data Aktual Produksi</h3>
                            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1 italic">Sesuai Tahapan: {division}</p>
                        </div>
                    </div>
                    
                    {isStenter ? (
                        renderStenterFields()
                    ) : (
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {Object.entries(divisionFields).map(([fieldName, config]) =>
                                renderField(fieldName, config)
                            )}
                        </div>
                    )}
                </div>
            )}

            {/* Submit Button */}
            {marketingOrder && auth.user.role === 'operator' && (
                <div className="flex justify-end pb-20">
                    <PrimaryButton
                        onClick={() => setShowConfirmModal(true)}
                        disabled={submitting}
                        className="bg-red-600 hover:bg-red-700 px-12 py-5 text-lg font-black uppercase italic tracking-widest shadow-2xl shadow-red-200 active:scale-95 transition-all"
                    >
                        Submit Laporan Produksi
                    </PrimaryButton>
                </div>
            )}

            {/* Confirmation Modal */}
            <Modal show={showConfirmModal} onClose={() => setShowConfirmModal(false)}>
                <div className="p-10 text-center">
                    <div className="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span className="text-red-600 text-3xl font-black">?</span>
                    </div>
                    <h3 className="text-2xl font-black text-gray-900 uppercase italic tracking-tighter mb-2 italic">Konfirmasi Data</h3>
                    <p className="text-gray-500 text-sm font-bold mb-10 leading-relaxed uppercase tracking-tight italic">
                        Apakah data aktual yang Anda masukkan sudah sesuai? Data akan langsung terkirim ke dashboard monitoring.
                    </p>
                    <div className="flex gap-4">
                        <button
                            onClick={() => setShowConfirmModal(false)}
                            className="flex-1 py-4 bg-gray-100 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-gray-200 transition-all"
                        >
                            BATAL
                        </button>
                        <PrimaryButton
                            onClick={handleSubmit}
                            disabled={submitting}
                            className="flex-1 py-4 bg-red-600 hover:bg-red-700 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg"
                        >
                            {submitting ? 'Mengirim...' : 'YA, KIRIM'}
                        </PrimaryButton>
                    </div>
                </div>
            </Modal>
        </>
    );
}