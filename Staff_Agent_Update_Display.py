from logging import root
import os
from pkgutil import get_data
from tkinter import *
from tkinter import messagebox
from PIL import Image, ImageTk
import mysql.connector
import pandas as pd # type: ignore
from fpdf import FPDF # type: ignore
from datetime import datetime

# Constants
COLOR_BORD = "#800000"
FONT_TITLE = ("Helvetica", 22, "bold")

def display_all_staff():
    root = Tk()
    root.title("Tous les Agents - Portrait")
    root.geometry("1100x700")
    root.configure(bg="white")

    canvas = Canvas(root, bg="white")
    scrollbar = Scrollbar(root, orient=VERTICAL, command=canvas.yview)
    scrollable_frame = Frame(canvas, bg="white")
    scrollable_frame.bind("<Configure>", lambda e: canvas.configure(scrollregion=canvas.bbox("all")))

    canvas.create_window((0, 0), window=scrollable_frame, anchor="nw")
    canvas.configure(yscrollcommand=scrollbar.set)

    canvas.pack(side="left", fill="both", expand=True)
    scrollbar.pack(side="right", fill="y")

    def fetch_data():
        try:
            conn = mysql.connector.connect(host="localhost", user="root", password="", database="unisecureid_db")
            cursor = conn.cursor()
            cursor.execute("SELECT id_personnel, nom_complet, poste, telephone, photo FROM personnels")
            result = cursor.fetchall()
            conn.close()
            return result
        except Exception as e:
            messagebox.showerror("Erreur", str(e))
            return []

    personnels = fetch_data()

    def refresh_display(filtered_data=None):
        for widget in scrollable_frame.winfo_children():
            widget.destroy()
        data = filtered_data if filtered_data else fetch_data()
        for idx, (id_p, nom, poste, tel, photo) in enumerate(data):
            create_agent_card(scrollable_frame, idx, id_p, nom, poste, tel, photo)

    def create_agent_card(parent, idx, id_p, nom, poste, tel, photo):
        card = Frame(parent, bd=2, relief="solid", bg="white", padx=10, pady=10)
        card.grid(row=idx // 3, column=idx % 3, padx=10, pady=10)

        img_path = os.path.join("photos_personnel", photo)
        try:
            img = Image.open(img_path).resize((160, 190))
        except:
            img = Image.new("RGB", (160, 190), color=(200, 200, 200))
        img = ImageTk.PhotoImage(img)

        Label(card, image=img, bg="white").pack()
        card.image = img

        Label(card, text=nom, font=("Helvetica", 12, "bold"), bg="white").pack(pady=(5, 0))
        Label(card, text=f"Poste: {poste}", font=("Helvetica", 11), bg="white").pack()
        Label(card, text=f"Tél: {tel}", font=("Helvetica", 11), bg="white").pack()

        def make_update():
            edit = Toplevel(root)
            edit.title("Modifier Agent")
            edit.geometry("500x400")
            edit.configure(bg="white")

            Label(edit, text="Modification du Personnel", font=FONT_TITLE, bg="white", fg=COLOR_BORD).pack(pady=20)

            def champ(label, val):
                Label(edit, text=label, font=("Helvetica", 13), bg="white").pack()
                entry = Entry(edit, font=("Helvetica", 13))
                entry.pack(pady=5, fill=X, padx=50)
                entry.insert(0, val)
                return entry

            nom_entry = champ("Nom complet :", nom)
            poste_entry = champ("Poste :", poste)
            tel_entry = champ("Téléphone :", tel)

            def save_modif():
                new_nom = nom_entry.get()
                new_poste = poste_entry.get()
                new_tel = tel_entry.get()
                if not (new_nom and new_poste and new_tel):
                    messagebox.showwarning("Champs manquants", "Tous les champs sont requis.")
                    return
                try:
                    conn = mysql.connector.connect(host="localhost", user="root", password="", database="unisecureid_db")
                    cursor = conn.cursor()
                    cursor.execute("UPDATE personnels SET nom_complet=%s, poste=%s, telephone=%s WHERE id_personnel=%s",
                                   (new_nom, new_poste, new_tel, id_p))
                    conn.commit()
                    conn.close()
                    messagebox.showinfo("Succès", "Données mises à jour.")
                    edit.destroy()
                    refresh_display()
                except Exception as e:
                    messagebox.showerror("Erreur", str(e))

            Button(edit, text="💾 Enregistrer", command=save_modif, bg=COLOR_BORD, fg="white", font=("Helvetica", 13)).pack(pady=20)

        def make_delete():
            if messagebox.askyesno("Confirmer", f"Voulez-vous supprimer {nom} ?"):
                try:
                    conn = mysql.connector.connect(host="localhost", user="root", password="", database="unisecureid_db")
                    cursor = conn.cursor()
                    cursor.execute("DELETE FROM personnels WHERE id_personnel = %s", (id_p,))
                    conn.commit()
                    conn.close()
                    messagebox.showinfo("Supprimé", f"{nom} a été supprimé.")
                    refresh_display()
                except Exception as e:
                    messagebox.showerror("Erreur", str(e))

        Button(card, text="Modifier", command=make_update, bg="blue", fg="white").pack(side="left", padx=5, pady=5)
        Button(card, text="Supprimer", command=make_delete, bg="red", fg="white").pack(side="right", padx=5, pady=5)

   

    def export_pdf():
        data = [(x[1], x[2], x[3]) for x in personnels]
        pdf = FPDF()
        pdf.add_page()
        pdf.set_font("Arial", "B", 16)
        pdf.cell(0, 10, "Liste du Personnel", ln=True, align="C")
        pdf.ln(10)
        pdf.set_font("Arial", "", 12)
        for idx, (nom, poste, tel) in enumerate(data, 1):
            pdf.cell(0, 10, f"{idx}. {nom} - {poste} - {tel}", ln=True)
        pdf.output("agents_personnels.pdf")
        messagebox.showinfo("Export PDF", "Exporté avec succès en agents_personnels.pdf")

    Button(root, text="🧾 Exporter PDF", command=export_pdf, bg=COLOR_BORD, fg="white").pack(pady=5)
    Button(root, text="🧾 PDF avec photos", command=export_pdf_with_photos, bg=COLOR_BORD, fg="white").pack(pady=5)

    # Search bar
    search_frame = Frame(root, bg="white")
    search_frame.pack(pady=10)

    search_entry = Entry(search_frame, font=("Helvetica", 13), width=40)
    search_entry.pack(side=LEFT, padx=5)

    def filter_staff():
        query = search_entry.get().lower()
        filtered = [row for row in personnels if query in row[1].lower() or query in row[2].lower()]
        refresh_display(filtered)

    Button(search_frame, text="🔍 Rechercher", command=filter_staff,
           bg=COLOR_BORD, fg="white", font=("Helvetica", 12)).pack(side=LEFT)

    refresh_display()
    root.mainloop()
    
    

def export_pdf_with_photos():
    data = get_data()
    pdf = FPDF()
    pdf.add_page()
    pdf.set_font("Arial", "B", 16)
    pdf.cell(0, 10, "LISTE DU PERSONNEL", ln=True, align="C")
    pdf.ln(10)

    pdf.set_font("Arial", "B", 12)
    pdf.cell(35, 10, "Photo", 1)
    pdf.cell(50, 10, "Nom Complet", 1)
    pdf.cell(50, 10, "Poste", 1)
    pdf.cell(50, 10, "Téléphone", 1)
    pdf.ln()

    for nom, poste, tel, photo in [(x[1], x[2], x[3], x[4]) for x in data]:
        photo_path = os.path.join("photos_personnel", photo)
        resized_path = os.path.join("photos_personnel", f"resized_{photo}")
        try:
            img = Image.open(photo_path)
            img = img.resize((20, 25))
            img.save(resized_path)
        except:
            resized_path = ""

        x, y = pdf.get_x(), pdf.get_y()
        pdf.cell(35, 25, "", 1)
        if os.path.exists(resized_path):
            pdf.image(resized_path, x + 7, y + 3, w=20, h=20)
        pdf.set_xy(x + 35, y)
        pdf.cell(50, 25, nom, 1)
        pdf.cell(50, 25, poste, 1)
        pdf.cell(50, 25, tel, 1)
        pdf.ln()

    pdf.ln(8)
    pdf.set_font("Arial", "", 12)
    today = datetime.now().strftime("%d/%m/%Y")
    pdf.cell(0, 10, f"Fait à ......................, le {today}", ln=True, align="R")

    pdf.output("agents_personnels_avec_photos.pdf")
    messagebox.showinfo("Export PDF", "PDF généré avec photos et tableau avec succès.")
   


display_all_staff()

