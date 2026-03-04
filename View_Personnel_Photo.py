
from tkinter import *
from tkinter import messagebox, simpledialog
from PIL import Image, ImageTk
import os
import mysql.connector

COLOR_BORD = "#800000"

def afficher_photo_personnel():
    root = Tk()
    root.withdraw()
    
    try:
        id_personnel = simpledialog.askinteger("Afficher Photo", "Entrez l'ID du personnel")
        if not id_personnel:
            return
        
        conn = mysql.connector.connect(
            host="localhost", user="root", password="", database="unisecureid_db"
        )
        cursor = conn.cursor()
        cursor.execute("SELECT nom_complet, photo FROM personnels WHERE id_personnel = %s", (id_personnel,))
        result = cursor.fetchone()
        conn.close()

        if not result:
            messagebox.showerror("Erreur", f"Aucun personnel trouvé avec l'ID {id_personnel}")
            return

        nom, photo_path = result
        full_path = os.path.join("photos_personnel", photo_path)
        if not os.path.exists(full_path):
            messagebox.showerror("Fichier introuvable", f"Le fichier photo {full_path} est introuvable.")
            return

        win = Toplevel()
        win.title(f"Photo de {nom}")
        win.geometry("400x400")
        win.configure(bg="white")

        img = Image.open(full_path)
        img = img.resize((300, 300))
        photo = ImageTk.PhotoImage(img)

        Label(win, text=nom, font=("Helvetica", 18, "bold"), bg="white", fg=COLOR_BORD).pack(pady=10)
        Label(win, image=photo, bg="white").pack(pady=10)

        win.mainloop()
    except Exception as e:
        messagebox.showerror("Erreur", str(e))

if __name__ == "__main__":
    afficher_photo_personnel()
