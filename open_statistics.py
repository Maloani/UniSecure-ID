import tkinter as tk
from tkinter import messagebox
import matplotlib.pyplot as plt
from matplotlib.backends.backend_tkagg import FigureCanvasTkAgg
import pandas as pd
import mysql.connector
from datetime import datetime, timedelta

# Connexion à la base de données
def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",   # 🔁 Remplace par ton mot de passe si nécessaire
        database="unisecureid_db"
    )

# Récupération des données depuis la base
def fetch_statistics():
    conn = get_db_connection()
    cursor = conn.cursor()

    data = {
        "date": [],
        "utilisateurs": [],
        "personnel": [],
        "presence": []
    }

    for i in range(6, -1, -1):
        date_check = (datetime.now().date() - timedelta(days=i))

        cursor.execute("SELECT COUNT(*) FROM users WHERE DATE(created_at) = %s", (date_check,))
        utilisateurs = cursor.fetchone()[0]

        cursor.execute("SELECT COUNT(*) FROM personnels WHERE DATE(date_ajout) = %s", (date_check,))
        personnel = cursor.fetchone()[0]

        cursor.execute("SELECT COUNT(*) FROM presences WHERE DATE(date_heure) = %s", (date_check,))
        presence = cursor.fetchone()[0]

        data["date"].append(date_check)
        data["utilisateurs"].append(utilisateurs)
        data["personnel"].append(personnel)
        data["presence"].append(presence)

    cursor.close()
    conn.close()
    return pd.DataFrame(data)

# Affichage de la fenêtre de statistiques
def open_statistics_dashboard():
    df_stats = fetch_statistics()

    stats_window = tk.Toplevel()
    stats_window.title("📊 Statistiques")
    stats_window.geometry("850x600")
    stats_window.configure(bg="#2E2E2E")

    tk.Label(stats_window, text="Statistiques Générales", font=("Helvetica", 18, "bold"), fg="white", bg="#2E2E2E").pack(pady=10)

    resume = f"""
👥 Utilisateurs aujourd'hui : {df_stats.iloc[-1]['utilisateurs']}
🏫 Personnel aujourd'hui : {df_stats.iloc[-1]['personnel']}
🟢 Présents aujourd'hui : {df_stats.iloc[-1]['presence']}
"""
    tk.Label(stats_window, text=resume, font=("Courier", 14), fg="white", bg="#2E2E2E", justify="left").pack()

    fig, ax = plt.subplots(figsize=(7, 3))
    ax.plot(df_stats["date"], df_stats["utilisateurs"], label="Utilisateurs", marker="o")
    ax.plot(df_stats["date"], df_stats["presence"], label="Présents", marker="x")
    ax.set_title("Évolution des Effectifs")
    ax.set_xlabel("Date")
    ax.set_ylabel("Nombre")
    ax.legend()
    fig.autofmt_xdate()

    canvas = FigureCanvasTkAgg(fig, master=stats_window)
    canvas.draw()
    canvas.get_tk_widget().pack(pady=10)

    def export_stats():
        df_stats.to_csv("statistiques_export.csv", index=False)
        messagebox.showinfo("Exportation réussie", "✅ Fichier enregistré : statistiques_export.csv")

    tk.Button(stats_window, text="📤 Exporter en CSV", command=export_stats, bg="#00796B", fg="white").pack(pady=5)
    tk.Button(stats_window, text="Fermer", command=stats_window.destroy, bg="#B71C1C", fg="white").pack(pady=5)

# Lancement manuel uniquement si exécution directe
if __name__ == "__main__":
    root = tk.Tk()
    root.withdraw()  # Cache la fenêtre principale
    open_statistics_dashboard()
    root.mainloop()
