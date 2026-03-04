from tkinter import *
from tkinter import ttk, messagebox
import mysql.connector
import cv2
import os
from PIL import Image, ImageTk
import subprocess
import sys
import threading
import time
import winsound

COLOR_BORD = "#800000"
FONT_TITLE = ("Helvetica", 22, "bold")

def open_update_display():
    # Petite fenêtre de chargement
    loading_win = Toplevel()
    loading_win.title("Chargement...")
    loading_win.geometry("350x120")
    loading_win.configure(bg="white")
    loading_win.resizable(False, False)

    try:
        icon_img = PhotoImage(file="update_icon.png")
        loading_win.iconphoto(False, icon_img)
    except:
        pass

    Label(loading_win, text="🕒 Chargement de l’interface de mise à jour...",
          font=("Helvetica", 12), bg="white", fg=COLOR_BORD).pack(pady=30)

    def launch_script():
        try:
            winsound.Beep(1000, 300)
            time.sleep(1.5)
            subprocess.Popen([sys.executable, "Staff_Agent_Update_Display.py"])
            loading_win.destroy()
        except Exception as e:
            loading_win.destroy()
            messagebox.showerror("Erreur", f"Impossible d’ouvrir l’interface : {e}")

    threading.Thread(target=launch_script).start()


def open_staff_management():
    global win  # Pour rendre 'win' accessible à open_update_display
    win = Toplevel()
    win.title("Gestion du Personnel")
    win.state('zoomed')
    win.configure(bg="white")

    Label(win, text="📋 Gestion du Personnel de l'Institution",
          font=FONT_TITLE, bg="white", fg=COLOR_BORD).pack(pady=20)

    columns = ("id", "nom_complet", "poste", "telephone")
    tree = ttk.Treeview(win, columns=columns, show="headings")
    for col in columns:
        tree.heading(col, text=col.capitalize())
        tree.column(col, anchor=CENTER, width=140)
    tree.pack(pady=10, fill="x", padx=40)

    def refresh_table():
        for row in tree.get_children():
            tree.delete(row)
        try:
            conn = mysql.connector.connect(
                host="localhost", user="root", password="", database="unisecureid_db"
            )
            cursor = conn.cursor()
            cursor.execute("SELECT id_personnel, nom_complet, poste, telephone FROM personnels")
            for row in cursor.fetchall():
                tree.insert("", "end", values=row)
            conn.close()
        except Exception as e:
            messagebox.showerror("Erreur", str(e))

    def capture_confirmed_photo(id_personnel):
        folder = "photos_personnel"
        os.makedirs(folder, exist_ok=True)

        window = Toplevel()
        window.title("Capture Visage - UniSecure ID")
        window.configure(bg="white")

        lmain = Label(window)
        lmain.pack()
        capture_ready = [False]

        def detect_camera_index():
            for i in range(4):
                cam = cv2.VideoCapture(i)
                if cam.isOpened():
                    ret, _ = cam.read()
                    cam.release()
                    if ret:
                        return i
            return -1

        camera_index = detect_camera_index()
        if camera_index == -1:
            messagebox.showerror("Erreur", "Aucune caméra détectée.")
            window.destroy()
            return

        face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
        cap = cv2.VideoCapture(camera_index)

        def show_frame():
            ret, frame = cap.read()
            if not ret:
                return

            faces = face_cascade.detectMultiScale(frame, 1.3, 5)
            for (x, y, w, h) in faces:
                cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 255, 0), 2)
                capture_ready[0] = True

            img = cv2.cvtColor(frame, cv2.COLOR_BGR2RGBA)
            img = cv2.resize(img, (600, 400))
            photo = PhotoImage(master=window, data=cv2.imencode('.png', img)[1].tobytes())
            lmain.configure(image=photo)
            lmain.image = photo
            window.after(10, show_frame)

        def capture_photo():
            if not capture_ready[0]:
                messagebox.showwarning("Alerte", "Aucun visage bien cadré détecté.")
                return
            ret, frame = cap.read()
            if not ret:
                messagebox.showerror("Erreur", "Échec de la capture.")
                return

            faces = face_cascade.detectMultiScale(frame, 1.3, 5)
            for (x, y, w, h) in faces:
                face_img = frame[y:y + h, x:x + w]
                resized_face = cv2.resize(face_img, (150, 150))
                filename = f"agent_{id_personnel}.jpg"
                path = os.path.join(folder, filename)
                cv2.imwrite(path, resized_face)

                try:
                    conn = mysql.connector.connect(
                        host="localhost", user="root", password="", database="unisecureid_db"
                    )
                    cursor = conn.cursor()
                    cursor.execute("UPDATE personnels SET photo = %s WHERE id_personnel = %s",
                                   (filename, id_personnel))
                    conn.commit()
                    conn.close()
                    messagebox.showinfo("Succès", "Photo enregistrée avec succès.")
                    cap.release()
                    window.destroy()
                except Exception as e:
                    messagebox.showerror("Erreur DB", str(e))
                break

        Button(window, text="📸 Capturer le visage", command=capture_photo,
               bg=COLOR_BORD, fg="white", font=("Helvetica", 13)).pack(pady=10)
        show_frame()

    def add_staff_form():
        form = Toplevel(win)
        form.title("Ajouter un membre du personnel")
        form.geometry("550x500")
        form.configure(bg="white")

        Label(form, text="Ajout du Personnel", font=FONT_TITLE,
              bg="white", fg=COLOR_BORD).pack(pady=10)

        def field(label):
            Label(form, text=label, font=("Helvetica", 13), bg="white").pack()
            entry = Entry(form, font=("Helvetica", 13))
            entry.pack(pady=3, fill=X, padx=50)
            return entry

        nom_entry = field("Nom complet :")
        poste_entry = field("Poste occupé :")
        phone_entry = field("Téléphone :")

        def enregistrer():
            nom = nom_entry.get()
            poste = poste_entry.get()
            telephone = phone_entry.get()

            if not (nom and poste and telephone):
                messagebox.showwarning("Champs requis", "Tous les champs doivent être remplis.")
                return

            try:
                conn = mysql.connector.connect(
                    host="localhost", user="root", password="", database="unisecureid_db"
                )
                cursor = conn.cursor()
                cursor.execute("INSERT INTO personnels (nom_complet, poste, telephone, photo) VALUES (%s, %s, %s, %s)",
                               (nom, poste, telephone, "pending.jpg"))
                conn.commit()
                personnel_id = cursor.lastrowid
                conn.close()

                form.destroy()
                capture_confirmed_photo(personnel_id)
                refresh_table()
            except Exception as e:
                messagebox.showerror("Erreur", str(e))

        Button(form, text="📸 Enregistrer et capturer photo", command=enregistrer,
               bg=COLOR_BORD, fg="white", font=("Helvetica", 13)).pack(pady=20)

    Button(win, text="➕ Ajouter un membre du personnel", command=add_staff_form,
           bg=COLOR_BORD, fg="white", font=("Helvetica", 12)).pack(pady=15)

    Button(win, text="🛠️ Mise à jour du personnel", command=open_update_display,
           bg=COLOR_BORD, fg="white", font=("Helvetica", 12)).pack(pady=15)

    refresh_table()

# Appel principal
if __name__ == "__main__":
    root = Tk()
    root.withdraw()
    open_staff_management()
    root.mainloop()
