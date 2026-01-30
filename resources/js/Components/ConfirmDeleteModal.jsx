import { Fragment } from 'react';
import { Dialog, Transition } from '@headlessui/react';

export default function ConfirmDeleteModal({ isOpen, onClose, onConfirm, itemTitle }) {
    return (
        <Transition show={isOpen} as={Fragment}>
            <Dialog as="div" className="relative z-[999]" onClose={onClose}>
                <Transition.Child as={Fragment} enter="ease-out duration-300" enterFrom="opacity-0" enterTo="opacity-100" leave="ease-in duration-200" leaveFrom="opacity-100" leaveTo="opacity-0">
                    <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                </Transition.Child>

                <div className="fixed inset-0 z-10 overflow-y-auto">
                    <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <Transition.Child as={Fragment} enter="ease-out duration-300" enterFrom="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enterTo="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leaveFrom="opacity-100 translate-y-0 sm:scale-100" leaveTo="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <Dialog.Panel className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                <div className="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div className="sm:flex sm:items-start flex-col items-center">
                                        <div className="mx-auto flex h-24 w-24 flex-shrink-0 items-center justify-center rounded-full bg-orange-100 sm:mx-0">
                                            <span className="text-5xl text-orange-500">!</span>
                                        </div>
                                        <div className="mt-3 text-center sm:mt-5 w-full">
                                            <Dialog.Title as="h3" className="text-2xl font-bold leading-6 text-gray-900">
                                                Konfirmasi Hapus
                                            </Dialog.Title>
                                            <div className="mt-4">
                                                <p className="text-sm text-gray-500">
                                                    Apakah Anda yakin ingin menghapus order SAP <span className="font-bold text-gray-800">{itemTitle}</span>?
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="bg-gray-50 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6 justify-center gap-3">
                                    <button type="button" className="inline-flex w-full justify-center rounded-xl bg-red-500 px-8 py-3 text-sm font-bold text-white shadow-sm hover:bg-red-600 sm:w-auto" onClick={onConfirm}>
                                        Ya, Hapus!
                                    </button>
                                    <button type="button" className="mt-3 inline-flex w-full justify-center rounded-xl bg-slate-500 px-8 py-3 text-sm font-bold text-white shadow-sm hover:bg-slate-600 sm:mt-0 sm:w-auto" onClick={onClose}>
                                        Batal
                                    </button>
                                </div>
                            </Dialog.Panel>
                        </Transition.Child>
                    </div>
                </div>
            </Dialog>
        </Transition>
    );
}