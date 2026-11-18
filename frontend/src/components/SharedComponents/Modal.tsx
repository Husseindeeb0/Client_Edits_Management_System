import { useEffect, useRef } from 'react';
import type { ReactNode } from 'react';
import { X } from 'lucide-react';

interface ModalProps {
  isOpen: boolean;
  title: string;
  onClose: () => void;
  children: ReactNode;
}

/** Uses the browser's <dialog>, which already closes on Esc and keeps focus inside. */
export default function Modal({ isOpen, title, onClose, children }: ModalProps) {
  const dialogRef = useRef<HTMLDialogElement>(null);

  useEffect(() => {
    const dialog = dialogRef.current;

    if (isOpen && !dialog?.open) {
      dialog?.showModal();
    } else if (!isOpen && dialog?.open) {
      dialog.close();
    }
  }, [isOpen]);

  // Stop the page behind the modal from scrolling until it closes
  useEffect(() => {
    if (!isOpen) {
      return;
    }

    document.body.style.overflow = 'hidden';

    return () => {
      document.body.style.overflow = '';
    };
  }, [isOpen]);

  return (
    <dialog
      ref={dialogRef}
      onClose={onClose}
      className="m-auto w-[calc(100%-2rem)] max-w-lg rounded-lg bg-slate-900 p-0 shadow-xl backdrop:bg-black/60"
    >
      <div className="flex items-center justify-between border-b border-slate-800 px-5 py-4">
        <h2 className="text-base font-semibold text-slate-100">{title}</h2>
        <button type="button" onClick={onClose} className="rounded-md p-1 text-slate-400 hover:bg-slate-800" aria-label="Close">
          <X className="size-5" />
        </button>
      </div>

      {/* Rendered only while open, so a form inside starts fresh each time */}
      <div className="px-5 py-4">{isOpen && children}</div>
    </dialog>
  );
}
