
import winsound
import cv2
import os
import mysql.connector
from datetime import datetime, time
import tkinter as tk
from tkinter import messagebox
from PIL import Image, ImageTk

PHOTO_FOLDER = "photos_personnel"
DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "unisecureid_db"
}
STANDARD_SIZE = (150, 150)

def detect_camera_index():
    for i in range(4):
        cam = cv2.VideoCapture(i)
        if not cam.isOpened():
            continue
        ret, frame = cam.read()
        if ret:
            cam.release()
            return i
        cam.release()
    return -1

def recognize_and_confirm():
    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
    eye_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_eye.xml")

    camera_index = detect_camera_index()
    if camera_index == -1:
        messagebox.showerror("Erreur", "Aucune caméra détectée.")
        return

    cam = cv2.VideoCapture(camera_index)
    confirmed_person = {"id": None, "nom": None, "déjà_marqué": False}

    def match_face(face_img):
        best_match = None
        min_diff = float("inf")
        for filename in os.listdir(PHOTO_FOLDER):
            if filename.endswith(".jpg"):
                ref_path = os.path.join(PHOTO_FOLDER, filename)
                ref_img = cv2.imread(ref_path, cv2.IMREAD_GRAYSCALE)
                try:
                    ref_img = cv2.resize(ref_img, STANDARD_SIZE)
                    diff = cv2.norm(face_img, ref_img, cv2.NORM_L2)
                    if diff < min_diff:
                        min_diff = diff
                        best_match = filename
                except:
                    continue
        return best_match, min_diff

    def enregistrer_presence():
        if not confirmed_person["id"]:
            return

        heure_actuelle = datetime.now().time()
        if heure_actuelle > time(8, 0):
            messagebox.showwarning("Trop tard", "La présence ne peut être enregistrée après 8h00.")
            return

        try:
            conn = mysql.connector.connect(**DB_CONFIG)
            cursor = conn.cursor()
            now = datetime.now()
            today_start = now.replace(hour=0, minute=0, second=0, microsecond=0)
            today_end = now.replace(hour=23, minute=59, second=59, microsecond=999999)

            cursor.execute("""
                SELECT COUNT(*) FROM presences
                WHERE id_personnel = %s AND date_heure BETWEEN %s AND %s
            """, (confirmed_person["id"], today_start, today_end))

            if cursor.fetchone()[0] > 0:
                winsound.Beep(600, 500)
                messagebox.showinfo("Déjà détecté", f"{confirmed_person['nom']} est déjà marqué présent aujourd’hui.")
                confirmed_person["déjà_marqué"] = True
                conn.close()
                return

            cursor.execute("""
                INSERT INTO presences (id_personnel, nom_complet, date_heure)
                VALUES (%s, %s, %s)
            """, (confirmed_person["id"], confirmed_person["nom"], datetime.now()))
            conn.commit()
            winsound.Beep(1000, 300)
            messagebox.showinfo("Succès", f"Présence enregistrée pour {confirmed_person['nom']}")
            confirmed_person["déjà_marqué"] = True
            conn.close()
        except Exception as e:
            messagebox.showerror("Erreur DB", str(e))

    root = tk.Tk()
    root.title("Reconnaissance de Présence - UniSecure ID")
    canvas = tk.Canvas(root, width=640, height=480)
    canvas.pack()

    def update_frame():
        ret, frame = cam.read()
        if not ret:
            root.after(30, update_frame)
            return

        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5, minSize=(120, 120))

        label = "Aucun visage"
        color = (0, 0, 255)

        for (x, y, w, h) in faces:
            face_img = gray[y:y+h, x:x+w]
            eyes = eye_cascade.detectMultiScale(face_img)
            if len(eyes) >= 1:
                face_img = cv2.resize(face_img, STANDARD_SIZE)
                best_match, min_diff = match_face(face_img)

                if best_match and min_diff < 2000:
                    agent_id = best_match.split("_")[1].split(".")[0]
                    try:
                        conn = mysql.connector.connect(**DB_CONFIG)
                        cursor = conn.cursor()
                        cursor.execute("SELECT nom_complet FROM personnels WHERE id_personnel = %s", (agent_id,))
                        result = cursor.fetchone()
                        conn.close()
                        if result:
                            if not confirmed_person["déjà_marqué"]:
                                confirmed_person["id"] = agent_id
                                confirmed_person["nom"] = result[0]
                                enregistrer_presence()
                            label = f"✔ {result[0]}"
                            color = (0, 255, 0)
                    except Exception as e:
                        label = "Erreur DB"
                else:
                    label = "Inconnu"
                    confirmed_person["id"] = None
                    confirmed_person["nom"] = None
                    color = (0, 0, 255)

            cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)
            cv2.putText(frame, label, (x, y-10), cv2.FONT_HERSHEY_SIMPLEX, 0.9, color, 2)

        image = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
        image = cv2.resize(image, (640, 480))
        imgtk = ImageTk.PhotoImage(Image.fromarray(image))
        canvas.imgtk = imgtk
        canvas.create_image(0, 0, anchor=tk.NW, image=imgtk)

        root.after(30, update_frame)

    update_frame()
    root.mainloop()
    cam.release()
    cv2.destroyAllWindows()

if __name__ == "__main__":
    recognize_and_confirm()